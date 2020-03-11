<?php

namespace App\Service;

use App\Entity\ArchiveBoard;
use App\Entity\ArchiveColumn;
use App\Entity\ArchiveTicket;
use App\Entity\Board;
use App\Entity\Column;
use App\Entity\Ticket;
use App\Entity\Voting;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class Archivist
{
    private $user = null;
    private $entityManager = null;

    public function __construct(EntityManagerInterface $entityManager,  UserInterface $user)
    {
        $this->entityManager = $entityManager;
        $this->user = $user;
    }

    public function archiveBoard(Board $board)
    {
        $archiveBoard = $this->getDoctrine()->getRepository(ArchiveBoard::class)->find($board->getId());

        if (null === $archiveBoard) {
            $archiveBoard = new ArchiveBoard();
            $archiveBoard->setId($board->getId());
            $archiveBoard->setName($board->getName());
            $archiveBoard->setCreator($this->user);
            $archiveBoard->setCreated(new \DateTime());

            $this->entityManager->persist($archiveBoard);
        }
        
        foreach ($board->getColumns() as $column) {
            $this->archiveColumn($column, $archiveBoard);
        }
        $this->entityManager->persist($archiveBoard);
        $this->entityManager->flush();
        /*
        $columns = $board->getColumns();
        $archiveColumns = new ArrayCollection();
        foreach ($columns as $column) {
            $archiveColumn = new ArchiveColumn();
            $tickets = $column->getTickets();

            $archiveTickets = new ArrayCollection();
            foreach ($tickets as $ticket) {
                $archiveTicket = new ArchiveTicket();
                $votings = $ticket->getVotings();

                $archiveVotings = new ArrayCollection();
php deptrac.phar analyze depfile.yml
                foreach ($votings as $voting) {
                    $archiveVoting = new ArchiveVoting();
                    $archiveVoting->setCreator($voting->getCreator());
                    $archiveVoting->setCreatephp deptrac.phar analyze depfile.ymld($voting->getCreated());

                }
            }
        }
        */
    }

    public function archiveColumn(Column $column, $archiveBoard = null)
    {
        /** kein Board übergeben */
        if (null === $archiveBoard) {
            /** aber board in der column */
            if (null !== $column->getBoard()) {
                $archiveBoard = $this->getDoctrine()->getRepository(ArchiveBoard::class)->find($column->getBoard()->getId());

                /** board an column noch nicht archiviert */
                if (null === $archiveBoard) {
                    $archiveBoard = new ArchiveBoard();
                    $archiveBoard->setCreator($column->getBoard()->getCreator());
                    $archiveBoard->setCreated($column->getBoard()->getCreated());
                    $archiveBoard->setName($column->getBoard()->getName());
                    $archiveBoard->setId($column->getBoard()->getId());
                    $archiveBoard->setModifier($column->getBoard()->getModifier());
                    $archiveBoard->setModified($column->getBoard()->getModified());
                }
            /** column hat auch kein board */
            } else {
                throw new InvalidArgumentException("Board in Column not exists!");
                /*
                $archiveBoard = new ArchiveBoard();
                $archiveBoard->setCreator($this->user);
                $archiveBoard->setCreated(new \DateTime());
                $archiveBoard->setName("Automatically Created ArchiveBoard");
                */
            }
        }

        $archiveColumn = $this->getDoctrine()->getRepository(ArchiveColumn::class)->find($column->getId());

        if (null === $archiveColumn) {
            $archiveColumn = new ArchiveColumn();
            $archiveColumn->setId($column->getId());
            $archiveColumn->setBoard($archiveBoard);
            $archiveColumn->setName($column->getName());
            $archiveColumn->setPriority($column->getPriority());
        }

        foreach ($archiveColumn->getTickets() as $ticket) {
            $this->archiveTicket($ticket, $archiveColumn);
        }
        $this->entityManager->persist($archiveColumn);
        $this->entityManager->flush();

        return $this;
    }

    public function archiveTicket(Ticket $ticket, $archiveColumn = null)
    {
        if (null === $archiveColumn) {
            // suche column im archiv mit column id
            if (null != $ticket->getColumn()) {
                $archiveColumn = $this->getDoctrine()->getRepository(ArchiveColumn::class)->find($ticket->getColumn()->getId());

                // column noch nicht archiviert
                if (null === $archiveColumn) {
                    $archiveColumn = new ArchiveColumn();
                    $archiveColumn->setId($ticket->getColumn()->getId());
                    $archiveColumn->setName($ticket->getColumn()->getName());
                    $archiveColumn->setPriority($ticket->getColumn()->getPriority());
                    $archiveColumn->setCreator($ticket->getColumn()->getCreator());
                    $archiveColumn->setCreated($ticket->getColumn()->getCreated());
                    $archiveColumn->setModifier($ticket->getColumn()->getModifier());
                    $archiveColumn->setModified($ticket->getColumn()->getModified());

                    $archiveBoard = $this->getDoctrine()->getRepository(ArchiveBoard::class)->find($ticket->getColumn()->getBoard()->getId());
                    $archiveColumn->setBoard($archiveBoard);
                }
            } else {
                throw new InvalidArgumentException("Column of Ticket not exists!");
            }
        }
        $archiveTicket = new ArchiveTicket();
        $archiveTicket->setColumn($archiveColumn);
        $archiveTicket->setContent($ticket->getContent());
        $archiveTicket->setCreated($ticket->getCreated());
        $archiveTicket->setCreator($ticket->getCreator());
        $archiveTicket->setId($ticket->getId());
        $archiveTicket->setModified($ticket->getModified());
        $archiveTicket->setModifier($ticket->getModifier());

        foreach ($ticket->getVotings() as $voting) {
            $this->archiveVoting($voting, $archiveTicket);
        }
        $this->entityManager->persist($archiveTicket);
        $this->entityManager->flush();
    }

    public function archiveVoting(Voting $voting, $archiveTicket = null)
    {
        /** archive ticket nicht übergeben */
        if (null === $archiveTicket) {
            if (null !== $voting->getTicket()) {
                $archiveTicket = $this->getDoctrine()->getRepository(ArchiveTicket::class)->find($voting->getTicket()->getId());
            } else if (null === $archiveTicket) {
                $archiveTicket = new ArchiveTicket();
                $archiveTicket->setId($voting->getTicket()->getId());
                $archiveTicket->setColumn($voting->getTicket()->getColumn());
                $archiveTicket->setContent($voting->getTicket()->getContent());
            }
        }
    }

    private function handleColumn($column)
    {

    }

    private function handleBoard($board)
    {

    }

    private function handleTicket($ticket)
    {

    }

    private function handleVoting($voting)
    {

    }

}