<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Ticket;
use App\Entity\Voting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\Publisher;
use Symfony\Component\Mercure\Update;

class VotingController extends AbstractController
{
    /**
     * @Route("/voting", name="app_votings")
     */
    public function index()
    {
        return $this->render('voting/index.html.twig', [
            'controller_name' => 'VotingController',
        ]);
    }

    /**
     * @Route("/vote", name="app_vote", methods={"POST"})
     */
    public function vote(Publisher $publisher, EntityManagerInterface $entityManager, Request $request)
    {
        $ticketId = $request->get('ticket_id');
        $value = $request->get('value');
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($ticketId);
        $votingContent = [];

        /* Check if voter already voted */
        /** @var Voting $voting */
        $voting = $this->getDoctrine()->getRepository(Voting::class)->findOneBy([
            'creator' => $this->getUser(),
            'ticket' => $ticket
        ]);

        /* never voted for this ticket */
        if (null == $voting) {
            $voting = new Voting();
            $voting->setCreator($this->getUser())
                ->setCreated(new \DateTime())
                ->setTicket($ticket)
                ->setPoints($value);

            $votingContent[$value] = 1;

            $entityManager->persist($voting);
            $entityManager->flush();
        /* voting changed */
        } elseif ($voting->getPoints() != $value) {
            $currentPoints = $voting->getPoints();
            $voting->setModified(new \DateTime())
                ->setModifier($this->getUser())
                ->setPoints($value);

            $votingContent[$value] = 1;
            $votingContent[$currentPoints] = -1;

            $entityManager->persist($voting);
            $entityManager->flush();
        /* remove voting */
        } else {
            $votingContent[$voting->getPoints()] = -1;

            $entityManager->remove($voting);
            $entityManager->flush();
        }

        /*
        $votings = $this->getDoctrine()->getRepository(Voting::class)->findBy(['ticket' => $ticket]);
        $summary = [];

        $additions = [];

        foreach ($votings as $voting) {
            if (!isset($summary[$voting->getPoints()])) {
                $summary[$voting->getPoints()] = 0;
            }
            $summary[$voting->getPoints()]++;
            if ($voting->getCreator() == $this->getUser()) {
                $additions['owner'] = $voting->getPoints();
            }
        }
        */
        $update = new Update(
            'https://retro.byte-artist.de/ticket/1',
            json_encode(
                [
                    'voting' => [
                        'votingContent' => $votingContent,
                        'ownerId' => $this->getUser()->getId(),
                        'ticketId' => $ticketId
                    ]
                ]
            )
        );

        $publisher($update);

//        return new JsonResponse(['success' => true, 'votings' => $summary, 'additions' => $additions]);
        return new JsonResponse([
            'success' => true,
            'votingContent' => $votingContent,
            'ownerId' => $this->getUser()->getId(),
            'ticketId' => $ticketId
        ]);
    }
}
