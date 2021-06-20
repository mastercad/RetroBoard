<?php

namespace App\Controller;

use App\Entity\TeamMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TeamMemberController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/team-member/role", name="team_member_role_save", methods={"PUT"})
     */
    public function saveRoleAction(EntityManagerInterface $entityManager, Request $request)
    {
        $identifier = (int) $request->request->get('id');
        $newRole = $request->request->get('newRole');

        $teamMember = $this->getDoctrine()->getRepository(TeamMember::class)->find($identifier);

        if (!$teamMember instanceof TeamMember) {
            $message = $this->translator->trans('team_member_not_found', ['id' => $identifier], 'errors');
            throw $this->createNotFoundException($message);
        }
        $this->denyAccessUnlessGranted('edit_role', $teamMember);

        $teamMember->setModified(new \DateTime());
        $teamMember->setModifier($this->getUser());
        $teamMember->setRoles([$newRole]);

        $entityManager->persist($teamMember);
        $entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'content' => $this->translator->trans('team_member_saved', [], 'messages')
            ]
        );
    }
}
