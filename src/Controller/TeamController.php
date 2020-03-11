<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\Team;
use App\Entity\TeamInvitation;
use App\Entity\TeamMember;
use App\Form\TeamType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamController extends AbstractController
{
    /**
     * @Route("/teams", name="teams", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $teamMembers = $entityManager->getRepository(TeamMember::class)->findBy(['member' => $this->getUser()]);

        if (empty($teamMembers)) {
            $teamMembers[]['team'] = $this->getDoctrine()->getRepository(Team::class)->findOneBy(['name' => 'Demo Team']);
        }

        return $this->render(
            'team/index.html.twig',
            [
                'teamMembers' => $teamMembers
            ]
        );
    }

    /**
     * @Route("/team/{id}", name="team_show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function showAction(int $id)
    {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Team '.$id.' not found!');
        }

        $this->denyAccessUnlessGranted('show', $team);

        return $this->render(
            'team/show.html.twig',
            [
                'team' => $team
            ]
        );
    }

    /**
     * @Route("/team/create", name="team_create", methods={"GET"})
     *
     * @param EntityManagerInterface $entityManager
     */
    public function create(EntityManagerInterface $entityManager) {
        $form = $this->createForm(TeamType::class);
        $team = new Team();

        $this->denyAccessUnlessGranted('create', $team);

        return $this->render(
            'team/create.html.twig',
            [
                'form' => $form->createView(),
                'team' => $team
            ]
        );
    }

    /**
     * @Route("/team/create/{id}", name="team_edit", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function editAction($id) {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException('No team id '.$id.' found.');
        }

        $this->denyAccessUnlessGranted('edit', $team);

        $form = $this->createForm(TeamType::class, $team, ['action' => $this->generateUrl('team_save')]);

        return $this->render(
            'team/create.html.twig',
            [
                'form' => $form->createView(),
                'team' => $team
            ]
        );
    }

    /**
     * @Route("/team/save", name="team_save", methods={"POST"})
     *
     * @param EntityManagerInterface $entityManager
     */
    public function save(EntityManagerInterface $entityManager, Request $request) {
        $id = (int)$request->request->get('team')['id'];

        $team = null;
        $teamMember = null;
        $errors = [];
        $success = false;

        if (0 < $id) {
            $team = $this->getDoctrine()->getRepository(Team::class)->find($id);
            $teamMember = $this->getDoctrine()->getRepository(TeamMember::class)->findOneBy(['member' => $this->getUser(), 'team' => $team]);
            $team->setModifier($this->getUser());
            $team->setModified(new \DateTime());

            $this->denyAccessUnlessGranted('edit', $team);
        } else {
            $team = new Team();
            $team->setCreator($this->getUser());
            $team->setCreated(new \DateTime());

            $this->denyAccessUnlessGranted('create', $team);
        }

        if (!$teamMember instanceof TeamMember) {
            $teamMember = new TeamMember();
            $teamMember->setTeam($team);
            $teamMember->setRoles(['ROLE_ADMIN']);
            $teamMember->setMember($this->getUser());
            $teamMember->setCreator($this->getUser());
            $teamMember->setCreated(new \DateTime());
        }

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted()
            && $form->isValid()
        ) {
            $team = $form->getData();

            $entityManager->persist($team);
            $entityManager->persist($teamMember);
            $entityManager->flush();
            $success = true;
        } else {
            $errors = $form->getErrors(true);
        }

        return new JsonResponse(['success' => $success, 'id' => $team->getId(), 'content' => $success ? 'Team erfolgreich angelegt!' : json_encode($errors)]);
    }

    /**
     * @Route("/team/invite", name="team_invite", methods={"POST"})
     *
     * @param \Swift_Mailer          $mailer
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     */
    public function inviteAction(\Swift_Mailer $mailer, Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($request->request->get('id'));

        $data = [];
        $teamInvitation = new TeamInvitation();
        $token = sha1(random_bytes(20));

        if (!empty($request->request->get('invitationId'))) {
            $teamInvitation = $this->getDoctrine()->getRepository(TeamInvitation::class)->find($request->request->get('invitationId'));
            $teamInvitation->setToken($token);
            $teamInvitation->setModifier($this->getUser());
            $teamInvitation->setModified(new \DateTime());

            $email = $teamInvitation->getEmail();
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

            $teamInvitation->setTeam($team);
            $teamInvitation->setEmail($email);
            $teamInvitation->setToken($token);
            $teamInvitation->setCreator($this->getUser());
            $teamInvitation->setCreated(new \DateTime());
        }

        $this->denyAccessUnlessGranted('create', $teamInvitation);

        try {
            $entityManager->persist($teamInvitation);
            $entityManager->flush();

            $data['code'] = 200;
            $data['success'] = true;
            $data['message'] = "success";
            $data['id'] = $teamInvitation->getId();
            $data['token'] = $teamInvitation->getToken();

            $message = new \Swift_Message('Invitation request for team "'.$team->getName().'" on https://retro.byte-artist.de');
            $message->setFrom('no-reply@byte-artist.de')
                ->setTo($email)
                ->setBcc('andreas.kempe@byte-artist.de')
                ->setBody(
                    $this->renderView(
                        'emails/invite-user-to-team.html.twig',
                        [
                            'email' => $email,
                            'team' => $team,
                            'token' => $token
                        ]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'emails/invite-user-to-team.txt.twig',
                        [
                            'email' => $email,
                            'team' => $team,
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
            $data['id'] = $teamInvitation->getId();
            $data['token'] = $teamInvitation->getToken();
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/team/invitation/{id}", name="team_invite_delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     *
     * @return void
     */
    public function deleteInvitationAction(EntityManagerInterface $entityManager, int $id)
    {
        $teamInvitation = $this->getDoctrine()->getRepository(TeamInvitation::class)->find($id);
        if (!$teamInvitation instanceof TeamInvitation) {
            return new JsonResponse(['success' => false, 'content' => 'Einladung nicht gefunden!']);
        }

        $this->denyAccessUnlessGranted('delete', $teamInvitation);

        $entityManager->remove($teamInvitation);
        $entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'content' => 'Einladung erfolgreich gelöscht!'
            ]
        );
    }

    /**
     * @Route("/team/member/{token}", name="team_member", methods={"GET"})
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     */
    public function memberAction(string $token, EntityManagerInterface $entityManager)
    {
        $teamInvitation = $this->getDoctrine()->getRepository(TeamInvitation::class)->findOneBy(['token' => $token]);

        if (!$teamInvitation instanceof TeamInvitation) {
            throw $this->createNotFoundException('Invitation not found!');
        }

        $this->denyAccessUnlessGranted('accept', $teamInvitation);

        $teamMember = new TeamMember();
        $teamMember->setTeam($teamInvitation->get());
        $teamMember->setMember($this->getUser());
        $teamMember->setCreator($this->getUser());
        $teamMember->setCreated(new \DateTime());
        $teamMember->setRoles(["ROLE_USER"]);

        try {
            $entityManager->persist($teamMember);
            $entityManager->remove($teamInvitation);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            // dürfte eigentlich nicht passieren da invitations gelöscht werden, wenn die subscribtion vollständig ablief
            // ist im grunde egal, da der user schon member ist und daher kann weiter geleitet werden.
        }
        return $this->redirectToRoute("team_show", ['id' => $teamMember->getTeam()->getId()]);
    }

    /**
     * @Route("/team/member/{id}", name="team_member_delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     *
     * @return void
     */
    public function deleteMemberAction(EntityManagerInterface $entityManager, int $id)
    {
        $teamMember = $this->getDoctrine()->getRepository(TeamMember::class)->find($id);

        if (!$teamMember instanceof TeamMember) {
            return new JsonResponse(['success' => false, 'content' => 'Mitglied für dieses Team nicht gefunden!']);
        }

        $entityManager->remove($teamMember);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'content' => 'Mitglied erfolgreich vom Team entfernt!']);
    }
}
