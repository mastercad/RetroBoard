<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    /**
     * @Route("/teams", name="teams", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager)
    {
        /*
        $boardMembers = $entityManager->getRepository(BoardMember::class)->findBy([
            'user' => $this->getUser()
        ]);

        if (empty($boardMembers)) {
            $boardMembers[]['board'] = $this->getDoctrine()->getRepository(Board::class)->findOneBy(['name' => 'Demo Board']);
        }

        return $this->render(
            'board/index.html.twig',
            [
                'boardMembers' => $boardMembers
            ]
        );
        */
        return $this->render(
            'team/index.html.twig'
        );
    }
}
