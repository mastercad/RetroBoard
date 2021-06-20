<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class TeamRepository extends EntityRepository
{
    public function findAllTeamsForUser(User $user)
    {
        $query = $this->createQueryBuilder('teams')
        ->innerJoin('teams.boardTeams', 'boardTeams')
        ->where(':user MEMBER OF teams.members')
        ->setParameter(':user', $user)
        ->groupBy('boardTeams')
        ->getQuery();

        return $query->getResult();
    }

    public function findAllKnownTeams(User $user)
    {
        return $this->createQueryBuilder('teams')
            ->where('member = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
