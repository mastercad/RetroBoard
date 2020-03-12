<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use App\Entity\Board;
use App\Entity\Column;

class TeamRepository extends EntityRepository
{
    public function findAllKnownTeams(User $user)
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
}