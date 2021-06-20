<?php

namespace App\Repository;

use App\Entity\Board;
use App\Entity\Column;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class BoardRepository extends EntityRepository
{
    public function findAllAvailableBoardsByUser(User $user)
    {
        $query = $this->createQueryBuilder('board')
            ->where(':user MEMBER OF board.boardTeams')
            ->orWhere(':user MEMBER OF board.boardMembers')
            ->setParameter('user', $user)
            ->getQuery();

        var_dump($query->getSQL());

        return $query->getResult();
    }

    public function findAllKnownMembers(User $user)
    {
        $statement = $this->getEntityManager()->getConnection()->prepare(
            'SELECT DISTINCT users_show.id, users_show.name
            FROM users
            INNER JOIN team_members ON team_members.`member`  =  users.id
            INNER JOIN board_teams ON board_teams.team  = team_members.team
            INNER JOIN board_teams AS board_teams_revert ON board_teams_revert.board = board_teams.board
            INNER JOIN team_members AS team_members_revert ON team_members_revert.team = board_teams_revert.team
            INNER JOIN users AS users_show ON users_show.id = team_members_revert.`member`
            WHERE users.id = :userId
            UNION
            SELECT  users_show.id, users_show.name
            FROM users
            INNER JOIN board_members ON board_members.`user` = users.id
            INNER JOIN board_members as board_members_show ON board_members_show.board = board_members.board
            INNER JOIN users as users_show ON users_show.id = board_members_show.`user`
            WHERE users.id = :userId'
        );

        $result = $statement->executeQuery(['userId' => $user->getId()]);

        return $result->fetchAllAssociative();
    }

    public function findAllKnownBoards(User $user)
    {
        $statement = $this->getEntityManager()->getConnection()->prepare(
            'SELECT boards.id, boards.name
                FROM boards
                LEFT JOIN board_members ON boards.id = board_members.board
                LEFT JOIN board_teams ON board_teams.board = boards.id
                LEFT JOIN teams ON teams.id = board_teams.team
                LEFT JOIN team_members ON team_members.team = teams.id
                WHERE boards.id IS NOT NULL AND (board_members.user = :userId OR team_members.member = :userId)
                GROUP BY boards.id, boards.name'
        );

        $result = $statement->executeQuery(['userId' => $user->getId()]);

        return $result->fetchAllAssociative();
    }

    public function findAllKnownTeams(User $user)
    {
        $statement = $this->getEntityManager()->getConnection()->prepare(
            'SELECT teams.id, teams.name
            FROM users
            INNER JOIN team_members ON team_members.`member`  =  users.id
            INNER JOIN board_teams ON board_teams.team  = team_members.team
            INNER JOIN board_teams AS board_teams_revert ON board_teams_revert.board = board_teams.board
            INNER JOIN teams ON teams.id = board_teams_revert.team
            WHERE users.id = :userId

            GROUP BY teams.id, teams.name

            UNION

            SELECT teams.id, teams.name FROM teams
            INNER JOIN team_members ON team_members.team = teams.id
            WHERE team_members.member = :userId'
        );

        $result = $statement->executeQuery(['userId' => $user->getId()]);

        return $result->fetchAllAssociative();
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
