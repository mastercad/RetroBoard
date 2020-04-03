<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\BoardMember;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Team;
use App\Entity\TeamInvitation;
use App\Entity\TeamMember;
use App\Entity\User;
use App\Form\TeamType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TeamController extends AbstractController
{
    private $validator;
    private $mailer;
    private $entityManager;
    private $translator;

    public function __construct(\Swift_Mailer $mailer, ValidatorInterface $validator, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/teams", name="teams", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $teamMembers = $entityManager->getRepository(TeamMember::class)->findBy(['member' => $this->getUser()]);

//        if (empty($teamMembers)) {
//            $teamMembers[]['team'] = $this->getDoctrine()->getRepository(Team::class)->findOneBy(['name' => 'Demo Team']);
//        }

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
    public function showAction(int $id) {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException($this->translator->trans('team_not_found', ['id' => $id], 'errors'));
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

        $knownMembers = $this->collectAllKnownMembers();

        return $this->render(
            'team/create.html.twig',
            [
                'form' => $form->createView(),
                'team' => $team,
                'knownMembers' => $knownMembers
            ]
        );
    }

    /**
     * @Route("/team/create/{id}", name="team_edit", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function editAction($id) {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException($this->translator->trans('team_not_found', ['id' => $id], 'errors'));
        }

        $this->denyAccessUnlessGranted('edit', $team);

        $form = $this->createForm(TeamType::class, $team, ['action' => $this->generateUrl('team_save')]);

        $knownMembers = $this->collectAllKnownMembers();

        return $this->render(
            'team/create.html.twig',
            [
                'form' => $form->createView(),
                'team' => $team,
                'knownMembers' => $knownMembers
            ]
        );
    }

    /**
     * @Route("/team/save", name="team_save", methods={"POST"})
     *
     * @param EntityManagerInterface $entityManager
     */
    public function save(EntityManagerInterface $entityManager, Request $request) {
        $teamRequestData = $request->request->get('team');
        $id = (int)$teamRequestData['id'];

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

        $team->setName($teamRequestData['name']);

        if (!$teamMember instanceof TeamMember) {
            $teamMember = new TeamMember();
            $teamMember->setTeam($team);
            $teamMember->setRoles(['ROLE_ADMIN']);
            $teamMember->setMember($this->getUser());
            $teamMember->setCreator($this->getUser());
            $teamMember->setCreated(new \DateTime());
        }

        $teamMemberSuccess = $this->manageTeamMember($request, $team);
        $teamInvitationsSuccess = $this->manageTeamInvitations($request, $team);

//        $teamRequestData = $request->request->get('team');

        if (!$teamMemberSuccess
            || !$teamInvitationsSuccess
        ) {
            if (isset($teamRequestData['members'])
                && is_array($teamRequestData['members'])
            ) {
                foreach ($teamRequestData['members'] as &$member) {
                    if ($member['teamMemberId'] instanceof TeamMember) {
                        $member['teamMemberId'] = $member['teamMemberId']->getId();
                    }
                }
            }

            if (isset($teamRequestData['invitations'])
                && is_array($teamRequestData['invitations'])
            ) {
                foreach ($teamRequestData['invitations'] as &$invitation) {
                    if ($invitation['teamInvitationId'] instanceof TeamInvitation) {
                        $this->sendInvitationEmail($team, $invitation['teamInvitationId']);

                        $invitation['id'] = $invitation['teamInvitationId']->getId();
                        $invitation['token'] = $invitation['teamInvitationId']->getToken();
                        $invitation['teamInvitationId'] = $invitation['teamInvitationId']->getId();
                    }
                }
            }
            return new JsonResponse(['success' => false, 'data' => $teamRequestData]);
        }

/*
        $members = [];
        if (isset($teamRequestData['members'])) {
            $members = $teamRequestData['members'];
            unset($teamRequestData['members']);
        }

        $invitations = [];
        if (isset($teamRequestData['invitations'])) {
            $invitations = $teamRequestData['invitations'];
            unset($teamRequestData['invitations']);
        }

        $request->request->set('team', $teamRequestData);

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);
*/
//        if ($form->isSubmitted()
//            && $form->isValid()
//        ) {
//            $team = $form->getData();

            $entityManager->persist($team);
            $entityManager->persist($teamMember);
            $entityManager->flush();

            $success = true;

            if (isset($teamRequestData['members'])
                && is_array($teamRequestData['members'])
            ) {
                foreach ($teamRequestData['members'] as &$member) {
                    if ($member['teamMemberId'] instanceof TeamMember) {
                        $member['teamMemberId'] = $member['teamMemberId']->getId();
                    }
                }
            }

            if (isset($teamRequestData['invitations'])
                && is_array($teamRequestData['invitations'])
            ) {
                foreach ($teamRequestData['invitations'] as &$invitation) {
                    if ($invitation['teamInvitationId'] instanceof TeamInvitation) {
                        $this->sendInvitationEmail($team, $invitation['teamInvitationId']);

                        $invitation['id'] = $invitation['teamInvitationId']->getId();
                        $invitation['token'] = $invitation['teamInvitationId']->getToken();
                        $invitation['teamInvitationId'] = $invitation['teamInvitationId']->getId();
                    }
                }
            }

            $teamRequestData['id'] = $team->getId();
//        } else {
//            $errors = $form->getErrors(true);
//        }

        return new JsonResponse([
            'success' => $success, 
            'id' => $team->getId(), 
            'data' => $teamRequestData,
            'content' => $success ? $this->translator->trans('team_created', [], 'messages') : json_encode($errors)]);
    }

    /**
     * Manage given member for team membership.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function manageTeamMember(Request $request, Team $team) : bool {

        $success = true;
        $teamRequestData = $request->request->get('team');
        if (!isset($teamRequestData['members'])) {
            return $success;
        }

        foreach ($teamRequestData['members'] as &$member) {
            $teamMember = null;
            $user = $this->getDoctrine()->getRepository(User::class)->find($member['userId']);
            if (empty($member['teamMemberId'])) {
                $teamMember = new TeamMember;
                $teamMember->setMember($user);
                $teamMember->setCreator($this->getUser());
                $teamMember->setCreated(new \DateTime());
                $teamMember->setTeam($team);
                $teamMember->setRoles($member['roles']);
                $member['teamMemberId'] = $teamMember;
            } else {
                $teamMember = $this->getDoctrine()->getRepository(TeamMember::class)->find($member['teamMemberId']);
                $teamMember->setRoles($member['roles']);
                $teamMember->setModifier($this->getUser());
                $teamMember->setModified(new \DateTime());
            }
            $this->entityManager->persist($teamMember);
        }

        $request->request->set('team', $teamRequestData);
        return $success;
    }

    /**
     * Manage given invitations for team membership.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function manageTeamInvitations(Request $request, Team $team) : bool {

        $success = true;

        $teamRequestData = $request->request->get('team');
        if (!isset($teamRequestData['invitations'])) {
            return $success;
        }

        foreach ($teamRequestData['invitations'] as &$invitation) {
            $result = $this->handleTeamInvitation($team, $invitation['email'], $invitation['teamInvitationId'], true);

            if ($result instanceof TeamInvitation) {
                $invitation['teamInvitationId'] = $result;
            } else {
                $success = false;
                $invitation['result'] = $result;
            }
        }

        $request->request->set('team', $teamRequestData);
        return $success;
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
    public function inviteAction(Request $request) {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($request->request->get('id'));

        $data = $this->handleTeamInvitation($team, $request->request->get('email'), $request->request->get('invitationId'));

        return new JsonResponse($data);
    }

    /**
     * Undocumented function
     *
     * @param Team $team
     * @param string $email
     * @param int $invitationId
     * @param boolean $postProcessed
     *
     * @return void
     */
    private function handleTeamInvitation(Team $team, $email, $invitationId = null, $postProcessed = false) {

        $teamInvitation = new TeamInvitation();
        $token = sha1(random_bytes(20));

        if (!empty($invitationId)) {
            $teamInvitation = $this->getDoctrine()->getRepository(TeamInvitation::class)->find($invitationId);
            $teamInvitation->setToken($token);
            $teamInvitation->setModifier($this->getUser());
            $teamInvitation->setModified(new \DateTime());

            $email = $teamInvitation->getEmail();
        } else {
            $emailConstraint = new Email();
            $emailConstraint->message = $this->translator->trans('email_invalid', [], 'errors');

            $errorList = $this->validator->validate(
                $email,
                $emailConstraint
            );

            if (0 < count($errorList)) {
//                throw new InvalidArgumentException("Email ".$email." invalid!");
                $data['code'] = 500;
                $data['content'] = $this->translator->trans('email_invalid', [], 'errors');
                $data['success'] = false;

                if ($postProcessed) {
                    return $data;
                } else {
                    return new JsonResponse($data);
                }
            }

            $teamInvitation->setTeam($team);
            $teamInvitation->setEmail($email);
            $teamInvitation->setToken($token);
            $teamInvitation->setCreator($this->getUser());
            $teamInvitation->setCreated(new \DateTime());
        }

        if ($team->getId()) {
            $this->denyAccessUnlessGranted('create', $teamInvitation);
        }

        try {
            $this->entityManager->persist($teamInvitation);

            $data['code'] = 200;
            $data['success'] = true;
            $data['content'] = $this->translator->trans('success', [], 'messages');
            $data['id'] = $teamInvitation->getId();
            $data['token'] = $teamInvitation->getToken();


            if (!$postProcessed) {
                $this->entityManager->flush();
                $this->sendInvitationEmail($teamInvitation);
            }
        } catch (UniqueConstraintViolationException $exception) {
            $data['code'] = 500;
            $data['success'] = false;
            $data['content'] = $this->translator->trans('team_already_invited', [], 'errors');
            $data['id'] = $teamInvitation->getId();
            $data['token'] = $teamInvitation->getToken();
        }

        if ($postProcessed) {
            return $teamInvitation;
        }

        return $data;
    }

    /**
     * Undocumented function
     *
     * @param TeamInvitation $teamInvitation
     *
     * @return void
     */
    private function sendInvitationEmail(TeamInvitation $teamInvitation) {

        /** @TODO translate */
        $message = new \Swift_Message('Invitation request team "'.$teamInvitation->getTeam()->getName().'" on https://retro.byte-artist.de');
        $message->setFrom('no-reply@byte-artist.de')
            ->setTo($teamInvitation->getEmail())
            ->setBcc('andreas.kempe@byte-artist.de')
            ->setBody(
                $this->renderView(
                    'emails/invite-user-to-team.html.twig',
                    [
                        'email' => $teamInvitation->getEmail(),
                        'team' => $teamInvitation->getTeam(),
                        'token' => $teamInvitation->getToken()
                    ]
                ),
                'text/html'
            )
            ->addPart(
                $this->renderView(
                    'emails/invite-user-to-team.txt.twig',
                    [
                        'email' => $teamInvitation->getEmail(),
                        'team' => $teamInvitation->getTeam(),
                        'token' => $teamInvitation->getToken()
                    ]
                ),
                'text/plain'
            );

        $this->mailer->send($message);

        return $this;
    }

    /**
     * @Route("/team/invitation/{id}", name="team_invite_delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $entityManager
     * @param integer $id
     *
     * @return void
     */
    public function deleteInvitationAction(EntityManagerInterface $entityManager, int $id) {
        $teamInvitation = $this->getDoctrine()->getRepository(TeamInvitation::class)->find($id);
        if (!$teamInvitation instanceof TeamInvitation) {
            return new JsonResponse(['success' => false, 'content' => $this->translator->trans('invitation_not_found', [], 'errors')]);
        }

        $this->denyAccessUnlessGranted('delete', $teamInvitation);

        $entityManager->remove($teamInvitation);
        $entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'content' => $this->translator->trans('invitation_removed', [], 'messages')
            ]
        );
    }

    /**
     * @Route("/team/{id}", name="team_delete", methods={"DELETE"})
     *
     * @param integer $id
     *
     * @return void
     */
    public function deleteAction(int $id)
    {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($id);
        if (!$team instanceof Team) {
            return new JsonResponse(['success' => false, 'content' => $this->translator->trans('team_not_found', ['id' => $id], 'errors')]);
        }

        $this->denyAccessUnlessGranted('delete', $team);

        $this->entityManager->remove($team);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'content' => $this->translator->trans('team_removed', [], 'messages')
            ]
        );
    }

    /**
     * @Route("/team/member", name="team_add_member", methods={"POST"})
     *
     * @param EntityManagerInterface $entityManager
     * @param Request                $request
     *
     * @return void
     */
    public function addMemberAction(EntityManagerInterface $entityManager, Request $request) {
        $data = [];
        $teamMember = new TeamMember();

        $team = $this->getDoctrine()->getRepository(Team::class)->find($request->request->get('teamId'));

        if ($team instanceof Team) {
            $this->denyAccessUnlessGranted('edit', $team);
        }

        try {
            if (!empty($request->request->get('teamMemberId'))) {
                $teamMember = $this->getDoctrine()->getRepository(TeamInvitation::class)->find($request->request->get('teamMemberId'));
                $this->denyAccessUnlessGranted('edit', $teamMember);
                $teamMember->addRole($request->request->get('userRole'));
                $teamMember->setModifier($this->getUser());
                $teamMember->setModified(new \DateTime());
            } else {
                $this->denyAccessUnlessGranted('create', $teamMember);
                $member = $this->getDoctrine()->getRepository(User::class)->find($request->request->get('userId'));

                $teamMember->setTeam($team);
                $teamMember->setMember($member);
                $teamMember->setRoles([$request->request->get('userRole')]);
                $teamMember->setCreator($this->getUser());
                $teamMember->setCreated(new \DateTime());
            }

            $entityManager->persist($teamMember);
            $entityManager->flush();

            $data['code'] = 200;
            $data['success'] = true;
            $data['content'] = $this->translator->trans('success', [], 'messages');
            $data['id'] = $teamMember->getId();
        } catch (UniqueConstraintViolationException $exception) {
            $data['code'] = 500;
            $data['success'] = false;
            $data['content'] = $this->translator->trans('member_already_added', [], 'errors');
            $data['id'] = $teamMember->getId();
        }
        return new JsonResponse($data);
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
            throw $this->createNotFoundException($this->translator->trans('invitation_not_found', ['id' => $id], 'errors'));
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
            return new JsonResponse(['success' => false, 'content' => $this->translator->trans('member_not_found', [], 'errors')]);
        }

        $entityManager->remove($teamMember);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'content' => $this->translator->trans('member_removed', [], 'messages')]);
    }

    private function collectAllKnownMembers() {
        return $this->getDoctrine()->getRepository(Board::class)->findAllKnownMembers($this->getUser());
    }
}
