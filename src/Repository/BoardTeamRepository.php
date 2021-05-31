<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityRepository;

class BoardTeamRepository extends EntityRepository
{
    public function findAllAvailableBoardsByUser(User $user)
    {
        $statement = $this->getEntityManager()->getConnection()->prepare(
            "SELECT board_teams.board FROM board_teams 
                INNER JOIN teams ON board_teams.team = teams.id 
                INNER JOIN team_members ON board_teams.team = team_members.team AND team_members.member = :userId 
            GROUP BY board_teams.board"
        );

        $statement->execute(['userId' => $user->getId()]);

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function findAllAvailableBoardsByUserORM(User $user)
    {
        $statement = $this->getEntityManager()->getConnection()->prepare(
            "SELECT board_teams.board FROM board_teams 
                INNER JOIN teams ON board_teams.team = teams.id 
                INNER JOIN team_members ON board_teams.team = team_members.team AND team_members.member = :userId 
            GROUP BY board_teams.board"
        );

        $statement->execute(['userId' => $user->getId()]);

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
