<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class BoardMemberRepository extends EntityRepository
{
    public function findAllKnownMembers(User $user)
    {
        return $this->createQueryBuilder('board_members')
            ->select(['user.name'])
            ->innerJoin('\App\Entity\User', 'user')
            ->andWhere('board_members.user = :userId')
            ->setParameter('userId', $user->getId())
            ->groupBy('user.name')
            ->getQuery()
            ->getResult();
    }
}