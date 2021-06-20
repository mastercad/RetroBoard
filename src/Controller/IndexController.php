<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\BoardMember;
use App\Entity\BoardTeam;
use App\Entity\Team;
use App\Entity\User;
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
        $boards = [];

        if ($this->getUser() instanceof User) {
            $boardMembers = $entityManager->getRepository(BoardMember::class)->findBy(['user' => $this->getUser()]);

            foreach ($boardMembers as $boardMember) {
                $boards[$boardMember->getBoard()->getName()] = $boardMember->getBoard();
            }

            $boardTeams = $this->getDoctrine()->getRepository(BoardTeam::class)->findAllAvailableBoardsByUser(
                $this->getUser()
            );

            foreach ($boardTeams as $boardTeam) {
                $board = $this->getDoctrine()->getRepository(Board::class)->find($boardTeam['board']);
                $boards[$board->getName()] = $board;
            }
        }

        if (empty($boards)) {
            $boards['Demo Board'] = $this->getDoctrine()->getRepository(Board::class)->findOneBy(
                ['name' => 'Demo Board']
            );
        } elseif (isset($boards['Demo Board'])) {
            unset($boards['Demo Board']);
        }

        return $this->render(
            'index/index.html.twig',
            [
                'boards' => $boards,
            ]
        );
    }
}
