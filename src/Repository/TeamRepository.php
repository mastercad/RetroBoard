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
        return $this->createQueryBuilder('teams')
            ->where('member = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}