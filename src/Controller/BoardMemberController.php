<?php

namespace App\Controller;

use App\Entity\BoardMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BoardMemberController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/board-member/role", name="board_member_role_save", methods={"PUT"})
     */
    public function saveRoleAction(EntityManagerInterface $entityManager, Request $request)
    {
        $id = (int)$request->request->get('id');
        $newRole = $request->request->get('newRole');

        $boardMember = $this->getDoctrine()->getRepository(BoardMember::class)->find($id);

        if (!$boardMember instanceof BoardMember) {
            throw $this->createNotFoundException($this->translator->trans('board_member_not_found', ['id' => $id], 'errors'));
        }
        $this->denyAccessUnlessGranted('edit_role', $boardMember);

        $boardMember->setModified(new \DateTime());
        $boardMember->setModifier($this->getUser());
        $boardMember->setRoles([$newRole]);

        $entityManager->persist($boardMember);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'content' => $this->translator->trans('board_member_saved', [], 'messages')]);
    }
}
