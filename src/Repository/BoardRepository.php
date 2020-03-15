<?php
namespace App\Repository;

use App\Entity\Board;
use App\Entity\User;
use App\Entity\Column;
use Doctrine\ORM\EntityRepository;

class BoardRepository extends EntityRepository
{
    public function findAllAvailableBoardsByUser(User $user)
    {
        return $this->createQueryBuilder('board')
            ->innerJoin('board.teams', 'teams')
            ->addSelect('teams')
            ->getQuery()
            ->getResult();
    }

    public function findActive(int $boardId)
    {
        return $this->createQueryBuilder('board')
            ->leftJoin('board.columns', 'columns')
            ->innerJoin('board.creator', 'creator')
            ->leftJoin('board.modifier', 'modifier')
            ->leftJoin('columns.tickets', 'tickets')
            ->addSelect('tickets')
            ->addSelect('columns')
            ->addSelect('board')
            ->addSelect('creator')
            ->addSelect('modifier')
            ->andWhere('tickets.archived = 0 OR tickets IS NULL')
            ->andWhere('board.id = :id')
            ->setParameter('id', $boardId)
            ->getQuery()
            ->getResult();
    }

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