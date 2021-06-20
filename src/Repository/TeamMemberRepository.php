<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class TeamMemberRepository extends EntityRepository
{
    /**
     * SELECT team_members.member, team_members.team, users.name FROM
        (SELECT team_members.team FROM team_members WHERE team_members.member = 2) AS known_teams
       INNER JOIN team_members ON team_members.team IN (known_teams.team)
       INNER JOIN users ON users.id = team_members.member

       GROUP BY team_members.member, team_members.team, users.name
     *
     * @param User $user
     * @return void
     */
    public function findAllKnownMembers(User $user)
    {
        $statement = $this->getEntityManager()->getConnection()->prepare(
            "SELECT team_members.member, team_members.team, users.id, users.name FROM
                (SELECT team_members.team FROM team_members WHERE team_members.member = :userId) AS known_teams
            INNER JOIN team_members ON team_members.team IN (known_teams.team)
            INNER JOIN users ON users.id = team_members.member AND users.id != :userId

            GROUP BY team_members.member, team_members.team, users.id, users.name"
        );

        $statement->execute(['userId' => $user->getId()]);

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function findAllKnownTeams(User $user)
    {
        return $this->createQueryBuilder('team_members')
            ->select(['team.id', 'team.name'])
            ->innerJoin('team_members.team', 'team', Join::WITH, 'team.id = team_members.team')
            ->where('team_members.member = :user')
            ->setParameter('user', $user)
            ->groupBy('team.id', 'team.name')
            ->getQuery()
            ->getResult();
    }
}
