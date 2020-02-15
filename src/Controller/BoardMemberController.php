<?php

namespace App\Controller;

use App\Entity\BoardMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BoardMemberController extends AbstractController
{
    /**
     * @Route("/board-member/role", name="board_member_role_save", methods={"PUT"})
     */
    public function saveRoleAction(EntityManagerInterface $entityManager, Request $request)
    {
        $id = (int)$request->request->get('id');
        $newRole = $request->request->get('newRole');

        $boardMember = $this->getDoctrine()->getRepository(BoardMember::class)->find($id);

        if (!$boardMember instanceof BoardMember) {
            throw $this->createNotFoundException('BoardMember '.$id.' not found!');
        }
        $this->denyAccessUnlessGranted('edit_role', $boardMember);

        $boardMember->setModified(new \DateTime());
        $boardMember->setModifier($this->getUser());
        $boardMember->setRoles([$newRole]);

        $entityManager->persist($boardMember);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'content' => 'BoardMember erfolgreich gespeichert!']);
    }
}
