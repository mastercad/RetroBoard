<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\BoardMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(EntityManagerInterface $entityManager, Request $request)
    {
        $boardMembers = $entityManager->getRepository(BoardMember::class)->findBy(['user' => $this->getUser()]);

        if (empty($boardMembers)) {
            $boardMembers[]['board'] = $this->getDoctrine()->getRepository(Board::class)->findOneBy(['name' => 'Demo Board']);
        }

        return $this->render(
            'index/index.html.twig',
            [
                'boardMembers' => $boardMembers,
            ]
        );
    }
}
