<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Board;
use App\Entity\Column;

class TicketsRepository extends EntityRepository
{
    public function findAllTicketsByBoard(Board $board)
    {
        return $this->createQueryBuilder('ticket')
            ->innerJoin('ticket.column', 'column')
            ->innerJoin('column.board', 'board')
            ->innerJoin('ticket.creator', 'user_creator')
            ->leftJoin('ticket.modifier', 'user_modifier')
            ->addSelect('ticket')
            ->addSelect('column')
            ->addSelect('board')
            ->addSelect('user_creator')
            ->addSelect('user_modifier')
            ->andWhere('column.board = :board')
            ->orderBy('ticket.created', 'DESC')
            ->setParameter('board', $board)
            ->getQuery()
            ->getResult();
    }

    public function findAllTicketsByColumn(Column $column)
    {
        return $this->createQueryBuilder('ticket')
            ->innerJoin('ticket.column', 'column')
            ->innerJoin('column.board', 'board')
            ->innerJoin('ticket.creator', 'user_creator')
            ->leftJoin('ticket.modifier', 'user_modifier')
            ->addSelect('ticket')
            ->addSelect('column')
            ->addSelect('board')
            ->addSelect('user_creator')
            ->addSelect('user_modifier')
            ->andWhere('ticket.column = :column')
            ->orderBy('ticket.created', 'DESC')
            ->setParameter('column', $column)
            ->getQuery()
            ->getResult();
    }
}
