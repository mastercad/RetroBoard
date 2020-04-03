<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class BoardMemberRepository extends EntityRepository
{
    public function findAllKnownMembers(User $user)
    {
        /*
        $query = $this->createQueryBuilder('board_members')
            ->select(['user.id', 'user.name'])
            ->innerJoin('\App\Entity\User', 'user', Join::WITH, 'user.id = board_members.user')
            ->where('board_members.user = :user')
            ->setParameter('user', $user)
            ->groupBy('user.id', 'user.name')
            ->getQuery();

        return $query->getResult();
        */

        $statement = $this->getEntityManager()->getConnection()->prepare(
            "SELECT board_members.user, board_members.board, users.id, users.name FROM
                (SELECT board_members.board FROM board_members WHERE board_members.user = :userId) AS known_boards
            INNER JOIN board_members ON board_members.board IN (known_boards.board)
            INNER JOIN users ON users.id = board_members.user AND users.id != :userId

            GROUP BY board_members.user, board_members.board, users.id, users.name");

        $statement->execute(['userId' => $user->getId()]);

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
