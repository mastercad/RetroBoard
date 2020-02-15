<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Board;
use App\Entity\BoardInvitation;
use App\Entity\BoardMember;
use App\Entity\BoardSubscriber;
use App\Form\BoardType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BoardController extends AbstractController
{
    /**
     * @Route("/board", name="board", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $boardMembers = $entityManager->getRepository(BoardMember::class)->findBy([
            'user' => $this->getUser()
        ]);

        return $this->render(
            'board/index.html.twig',
            [
                'boardMembers' => $boardMembers
            ]
        );
    }
    
    /**
     * @Route("/board/{id}", name="board_show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function showAction(int $id)
    {
//        $board = $this->getDoctrine()->getRepository(Board::class)->findActive($id)[0];
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);

        if (!$board) {
            throw $this->createNotFoundException('Board '.$id.' not found!');
        }

        $this->denyAccessUnlessGranted('show', $board);

        return $this->render(
            'board/show.html.twig',
            [
                'board' => $board,
                'archived' => 0
            ]
        );
    }

    /**
     * @Route("/board/archive/{id}", name="board_show_archive", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function showArchiveAction(int $id)
    {
//        $board = $this->getDoctrine()->getRepository(Board::class)->findActive($id)[0];
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);

        if (!$board) {
            throw $this->createNotFoundException('Board '.$id.' not found!');
        }

        $this->denyAccessUnlessGranted('show', $board);

        return $this->render(
            'board/show.html.twig',
            [
                'board' => $board,
                'archived' => 1
            ]
        );
    }

    /**
     * @Route("/board/create", name="board_create", methods={"GET"})
     */
    public function createAction()
    {
        $form = $this->createForm(BoardType::class);
        $board = new Board();

        $this->denyAccessUnlessGranted('create', $board);

        return $this->render(
            'board/create.html.twig',
            [
                'form' => $form->createView(),
                'board' => $board
            ]
        );
    }

    /**
     * @Route("/board/create/{id}", name="board_edit", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function editAction($id)
    {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);

        if (!$board) {
            throw $this->createNotFoundException('No task found for is '.$id);
        }

        $this->denyAccessUnlessGranted('edit', $board);

        $form = $this->createForm(BoardType::class, $board, ['action' => $this->generateUrl('board_save')]);

        return $this->render(
            'board/create.html.twig',
            [
                'form' => $form->createView(),
                'board' => $board
            ]
        );
    }

    /**
     * @Route("/board/create", name="board_save", methods={"PUT", "POST"})
     */
    public function saveAction(EntityManagerInterface $entityManager, Request $request)
    {
        $id = (int)$request->request->get('board')['id'];

        $board = null;
        $boardMember = null;
        $errors = [];
        $success = false;

        if (0 < $id) {
            $board = $this->getDoctrine()->getRepository(Board::class)->find($id);
            $boardMember = $this->getDoctrine()->getRepository(BoardMember::class)->findOneBy(['user' => $this->getUser(), 'board' => $board]);
            $board->setModifier($this->getUser());
            $board->setModified(new \DateTime());

            $this->denyAccessUnlessGranted('edit', $board);
        } else {
            $board = new Board();
            $board->setCreator($this->getUser());
            $board->setCreated(new \DateTime());

            $this->denyAccessUnlessGranted('create', $board);
        }

        if (!$boardMember instanceof BoardMember) {
            $boardMember = new BoardMember();
            $boardMember->setBoard($board);
            $boardMember->setRoles(['ROLE_ADMIN']);
            $boardMember->setUser($this->getUser());
            $boardMember->setCreator($this->getUser());
            $boardMember->setCreated(new \DateTime());
        }

        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        if ($form->isSubmitted()
            && $form->isValid()
        ) {
            try {
                $board = $form->getData();

                $columns = $board->getColumns();

                foreach ($columns as $column) {
                    $column->setBoard($board);
                    $entityManager->persist($column);
                }

                $entityManager->persist($board);
                $entityManager->persist($boardMember);
                $entityManager->flush();
                $success = true;
            } catch (UniqueConstraintViolationException $exception) {
                $errors = [['message' => 'One or more Columns with the Same name already exists on this board!']];
            }
        } else {
            $errors = $form->getErrors(true);
        }

        return new JsonResponse(['success' => $success, 'id' => $board->getId(), 'content' => $success ? 'Ticket erfolgreich gespeichert!' : json_encode($errors)]);
    }

    /**
     * @Route("/board/invite", name="board_invite", methods={"POST"})
     *
     * @param \Swift_Mailer          $mailer
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     */
    public function inviteAction(\Swift_Mailer $mailer, Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($request->request->get('id'));

        $data = [];
        $boardInvitation = new BoardInvitation();
        $token = sha1(random_bytes(20));

        if (!empty($request->request->get('invitationId'))) {
            $boardInvitation = $this->getDoctrine()->getRepository(BoardInvitation::class)->find($request->request->get('invitationId'));
            $boardInvitation->setToken($token);
            $boardInvitation->setModifier($this->getUser());
            $boardInvitation->setModified(new \DateTime());

            $email = $boardInvitation->getEmail();
        } else {
            $email = $request->request->get('email');
            $emailConstraint = new Email();
            $emailConstraint->message = 'Invalid email address';

            $errorList = $validator->validate(
                $email,
                $emailConstraint
            );

            if (0 < count($errorList)) {
//                throw new InvalidArgumentException("Email ".$email." invalid!");
                $data['code'] = 500;
                $data['message'] = 'Invalid email address';
                $data['success'] = false;

                return new JsonResponse($data);
            }

            $boardInvitation->setBoard($board);
            $boardInvitation->setEmail($email);
            $boardInvitation->setToken($token);
            $boardInvitation->setCreator($this->getUser());
            $boardInvitation->setCreated(new \DateTime());
        }

        $this->denyAccessUnlessGranted('create', $boardInvitation);

        try {
            $entityManager->persist($boardInvitation);
            $entityManager->flush();

            $data['code'] = 200;
            $data['success'] = true;
            $data['message'] = "success";
            $data['id'] = $boardInvitation->getId();
            $data['token'] = $boardInvitation->getToken();

            $message = new \Swift_Message('Invitation request board "'.$board->getName().'" on https://retro.byte-artist.de');
            $message->setFrom('no-reply@byte-artist.de')
                ->setTo($email)
                ->setBcc('andreas.kempe@byte-artist.de')
                ->setBody(
                    $this->renderView(
                        'emails/invite-user.html.twig',
                        [
                            'email' => $email,
                            'board' => $board,
                            'token' => $token
                        ]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'emails/invite-user.txt.twig',
                        [
                            'email' => $email,
                            'board' => $board,
                            'token' => $token
                        ]
                    ),
                    'text/plain'
                );

            $mailer->send($message);
        } catch (UniqueConstraintViolationException $exception) {
            $data['code'] = 500;
            $data['success'] = false;
            $data['message'] = "email already invited";
            $data['id'] = $boardInvitation->getId();
            $data['token'] = $boardInvitation->getToken();
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/board/invitation/{id}", name="board_invite_delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     *
     * @return void
     */
    public function deleteInvitationAction(EntityManagerInterface $entityManager, int $id)
    {
        $boardInvitation = $this->getDoctrine()->getRepository(BoardInvitation::class)->find($id);
        if (!$boardInvitation instanceof BoardInvitation) {
            return new JsonResponse(['success' => false, 'content' => 'Einladung nicht gefunden!']);
        }

        $this->denyAccessUnlessGranted('delete', $boardInvitation);

        $entityManager->remove($boardInvitation);
        $entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'content' => 'Einladung erfolgreich gelöscht!'
            ]
        );
    }

    /**
     * @Route("/board/member/{token}", name="board_member", methods={"GET"})
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     */
    public function memberAction(string $token, EntityManagerInterface $entityManager)
    {
        $boardInvitation = $this->getDoctrine()->getRepository(BoardInvitation::class)->findOneBy(['token' => $token]);

        if (!$boardInvitation instanceof BoardInvitation) {
            throw $this->createNotFoundException('Invitation not found!');
        }

        $this->denyAccessUnlessGranted('accept', $boardInvitation);

        $boardMember = new BoardMember();
        $boardMember->setBoard($boardInvitation->getBoard());
        $boardMember->setUser($this->getUser());
        $boardMember->setCreator($this->getUser());
        $boardMember->setCreated(new \DateTime());
        $boardMember->setRoles(["ROLE_USER"]);

        $boardSubscriber = new BoardSubscriber();
        $boardSubscriber->setBoard($boardInvitation->getBoard());
        $boardSubscriber->setSubscriber($this->getUser());
        $boardSubscriber->setCreator($this->getUser());
        $boardSubscriber->setCreated(new \DateTime());

        try {
            $entityManager->persist($boardMember);
            $entityManager->persist($boardSubscriber);
            $entityManager->remove($boardInvitation);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            // dürfte eigentlich nicht passieren da invitations gelöscht werden, wenn die subscribtion vollständig ablief
            // ist im grunde egal, da der user schon member ist und daher kann weiter geleitet werden.
        }
        return $this->redirectToRoute("board_show", ['id' => $boardMember->getBoard()->getId()]);
    }

    /**
     * @Route("/board/member/{id}", name="board_member_delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     *
     * @return void
     */
    public function deleteMemberAction(EntityManagerInterface $entityManager, int $id)
    {
        $boardMember = $this->getDoctrine()->getRepository(BoardMember::class)->find($id);

        if (!$boardMember instanceof BoardMember) {
            return new JsonResponse(['success' => false, 'content' => 'Mitglied für dieses Board nicht gefunden!']);
        }

        $entityManager->remove($boardMember);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'content' => 'Mitglied erfolgreich vom Board entfernt!']);
    }

    /**
     * @Route("/board/subscribe/{id}", name="board_subscribe", methods={"GET"})
     *
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     * 
     * @return void
     */
    public function subscribeAction(EntityManagerInterface $entityManager, int $id)
    {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);
        $content = "";

        if (!$board instanceof Board) {
            return new JsonResponse(['success' => false, 'content' => 'Board nicht gefunden!']);
        }

        $boardSubscriber = $this->getDoctrine()->getRepository(BoardSubscriber::class)->findOneBy(
            [
                'board' => $board,
                'subscriber' => $this->getUser()
            ]
        );
        
        if (!$boardSubscriber instanceof BoardSubscriber) {
            $boardSubscriber = new BoardSubscriber();
            $boardSubscriber->setBoard($board);
            $boardSubscriber->setSubscriber($this->getUser());
            $boardSubscriber->setCreator($this->getUser());
            $boardSubscriber->setCreated(new \DateTime());

            $entityManager->persist($boardSubscriber);
            $content = "Board erfolgreich abboniert!";
        } else {
            $entityManager->remove($boardSubscriber);
            $content = "Board erfolgreich deabboniert!";
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true, 'content' => $content]);
    }
}
