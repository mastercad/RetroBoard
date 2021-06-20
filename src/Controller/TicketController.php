<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ticket;
use App\Entity\Board;
use App\Form\TicketFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Entity\Column;
use Countable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Publisher;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\Translation\TranslatorInterface;

class TicketController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /*
    public function __invoke(Publisher $publisher) : Response
    {
        $update = new Update(
            'https://retro.byte-artist.de/board/1',
            json_encode(['ich_bin' => 'durch'])
        );

        $publisher($update);

        return new Response('published');
    }
    */

    /**
     * @Route("/tickets", name="app_tickets")
     *
     * @return Response
     */
    public function index()
    {
        /** @var Board $board */
        $board = $this->getDoctrine()->getRepository(Board::class)->findOneBy(['id' => 1]);
        $tickets = $this->getDoctrine()->getRepository(Ticket::class)->findAllTicketsByBoard($board);

        return $this->render(
            'ticket/index.html.twig',
            [
                'tickets' => $tickets
            ]
        );
    }

    /**
    * @Route("/ticket/{id}", name="app_ticket_show", methods={"GET"}, requirements={"id"="\d+"})
    *
    */
    public function showTicket(int $id)
    {
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);

        if (! $ticket instanceof Ticket) {
            return new JsonResponse(
                [
                    'sucess' => false,
                    'content' => $this->translator->trans('ticket_not_found', ['id' => $id], 'errors')
                ]
            );
        }

        return $this->render(
            'ticket/show.html.twig',
            [
                'ticket' => $ticket,
                'archived' => 0
            ]
        );
    }

    /**
     * Returns the Edit template, filled out, if id set
     *
     * @Route("/ticket/load_edit_template", name="app_ticket_load_edit_template", methods={"GET"})
     *
     * @param Request $request Request from Frontend.
     *
     * @return Response
     */
    public function loadEditTicketTemplate(Request $request)
    {
        $ticketId = (int)$request->get('id');
        $ticket = null;

        if (0 < $ticketId) {
            $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($ticketId);
        } else {
            $ticket = new Ticket();
        }

        $form = $this->createForm(TicketFormType::class, $ticket);

        return $this->render(
            'ticket/edit.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @Route("/ticket/save", name="app_ticket_save", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createTicket(
        LoggerInterface $logger,
        \Swift_Mailer $mailer,
        Publisher $publisher,
        EntityManagerInterface $entityManager,
        Request $request
    ) {
        $column = $this->getDoctrine()->getRepository(Column::class)->find($request->get('column_id'));
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

        $ticket = new Ticket();
        $ticket->setCreator($user)
            ->setCreated(new \DateTime())
            ->setColumn($column);

        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->submit($request->request->get('ticket_form'));
        $result = false;

        if ($form->isValid()) {
            $entityManager->persist($ticket);
            $entityManager->flush($ticket);
            $result = true;

            $update = new Update(
                'https://retro.byte-artist.de/ticket/1',
                json_encode(
                    [
                        'ticket' => [
                            'ownerId' => $this->getUser()->getId(),
                            'create' => [
                                'columnId' => $ticket->getColumn()->getId(),
                                'ticketId' => $ticket->getId()
                            ]
                        ]
                    ]
                )
            );

            $publisher($update);
        } else {
            $result = $form->getErrors()->__toString();
        }

        $this->informSubscribers($logger, $mailer, $ticket);

        return new JsonResponse(
            [
                'success' => $result
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @Route("/ticket/save/{id}", name="app_ticket_update", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateTicket(Publisher $publisher, EntityManagerInterface $entityManager, Request $request, int $id)
    {
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);

        $ticket->setModifier($this->getUser())
            ->setModified(new \DateTime());

        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->submit(
            [
                'content' => base64_decode($request->get('content'))
            ]
        );

        $result = false;

        if ($form->isValid()) {
            $entityManager->persist($ticket);
            $entityManager->flush($ticket);
            $result = true;

            $update = new Update(
                'https://retro.byte-artist.de/ticket/1',
                json_encode(
                    [
                        'ticket' => [
                            'ownerId' => $this->getUser()->getId(),
                            'update' => [
                                'ticketId' => $ticket->getId()
                            ]
                        ]
                    ]
                )
            );

            $publisher($update);
        } else {
            $result = $form->getErrors()->__toString();
        }

        return new JsonResponse(
            [
                'success' => $result
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @Route("/ticket/delete/{id}", name="app_ticket_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     *
     * @return JsonResponse
     */
    public function deleteTicket(Publisher $publisher, EntityManagerInterface $entityManager, int $id)
    {
        /** @var Ticket $ticket */
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);

        if (!$ticket instanceof Ticket) {
            return new JsonResponse(
                [
                    'success' => 'false',
                    'content' => $this->translator->trans('ticket_not_found', ['id' => $id], 'errors')
                ]
            );
        }

        if ($ticket->getCreator() != $this->getUser()) {
            return new JsonResponse(
                [
                    'success' => 'false',
                    'content' => $this->translator->trans('ticket_deletion_not_allowed', [], 'errors')
                ]
            );
        }

        $entityManager->remove($ticket);
        $entityManager->flush($ticket);

        $update = new Update(
            'https://retro.byte-artist.de/ticket/',
            json_encode(
                [
                    'ticket' => [
                        'delete' => $id,
                        'ownerId' => $this->getUser()->getId(),
                    ]
                ]
            )
        );

        $publisher($update);

        return new JsonResponse(
            [
                'success' => true,
                'content' => $this->translator->trans('ticket_removed', [], 'messages')
            ]
        );
    }

    /**
     *
     * @Route("/ticket/archive/{id}", name="app_ticket_archive", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @return JsonResponse
     */
    public function archiveTicket(Publisher $publisher, EntityManagerInterface $entityManger, int $id)
    {
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);

        if (!$ticket instanceof Ticket) {
            return new JsonResponse(
                [
                    'success' => 'false',
                    'content' => $this->translator->trans('ticket_not_found', [], 'errors')
                ]
            );
        }

        $ticket->setArchived(true);
        $entityManger->persist($ticket);
        $entityManger->flush();

        $publisherMessage = new Update(
            'https://retro.byte-artist.de/ticket/',
            json_encode(
                [
                    'ticket' => [
                        'ownerId' => $this->getUser()->getId(),
                        'archive' => $id
                    ]
                ]
            )
        );

        $publisher($publisherMessage);

        return new JsonResponse(
            [
                'success' => true,
                'content' => $this->translator->trans('ticket_archived', ['id' => $id], 'messages')
            ]
        );
    }

    /**
     * Send notification to all board subscribers
     */
    private function informSubscribers(LoggerInterface $logger, \Swift_Mailer $mailer, $ticket)
    {
        /** @TODO translate */
        $board = $ticket->getColumn()->getBoard();
        $message = new \Swift_Message(
            'New Entry from '.$ticket->getCreator()->getName().' for board "'.$board->getName().
            '" on https://retro.byte-artist.de'
        );
        $message->setFrom('no-reply@byte-artist.de')
            ->setBcc('andreas.kempe@byte-artist.de')
            ->setBody(
                $this->renderView(
                    'emails/new-ticket-information.html.twig',
                    [
                        'ticket' => $ticket,
                        'board' => $board
                    ]
                ),
                'text/html'
            )
            ->addPart(
                $this->renderView(
                    'emails/new-ticket-information.txt.twig',
                    [
                        'ticket' => $ticket,
                        'board' => $board
                    ]
                ),
                'text/plain'
            );

        /** @var User $subscriber */
        foreach ($board->getBoardSubscribers() as $subscriber) {
            if ($subscriber->getSubscriber() != $this->getUser()) {
                $message->addTo($subscriber->getSubscriber()->getEmail());
            }
        }

        if ((is_array($message->getTo())
             || $message->getTo() instanceof Countable)
            && 0 < count($message->getTo())
        ) {
            $mailer->send($message);
        }

        return $this;
    }
}
