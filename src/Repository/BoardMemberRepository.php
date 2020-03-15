<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class BoardMemberRepository extends EntityRepository
{
    public function findAllKnownMembers(User $user)
    {
        $query = $this->createQueryBuilder('board_members')
            ->select(['user.id', 'user.name'])
            ->innerJoin('\App\Entity\User', 'user', Join::WITH, 'user.id = board_members.user')
            ->where('board_members.user = :user')
            ->setParameter('user', $user)
            ->groupBy('user.id', 'user.name')
            ->getQuery();

        return $query->getResult();
    }
}
