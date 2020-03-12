<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class TeamMemberRepository extends EntityRepository
{
    public function findAllKnownMembers(User $user)
    {
        return $this->createQueryBuilder('team_members')
            ->select([
//                'IDENTITY(team_members.id) as id',
//                'IDENTITY(team_members.team) as team',
//                'IDENTITY(team_members.member) as member',
//                'team_members.name'
                'team_members.member.id'
            ])
            ->andWhere(':user = team_members.member')
            ->setParameter('user', $user)
            ->groupBy('team_members.member')
            ->getQuery()
            ->getResult();
    }

    public function findAllKnownTeams(User $user)
    {
        return $this->createQueryBuilder('team_members')
            ->select(['team.name'])
            ->innerJoin('\App\Entity\Team', 'team', 'team.id = team_members.team')
            ->andWhere('team_members.member = :userId')
            ->setParameter('userId', $user->getId())
            ->groupBy('team.name')
            ->getQuery()
            ->getResult();
    }
}