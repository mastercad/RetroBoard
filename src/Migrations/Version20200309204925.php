<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200309204925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create Teams and Team_members tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `teams` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
              `creator` int unsigned NOT NULL,
              `created` datetime NOT NULL,
              `modifier` int unsigned DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `teams_id_IDX` (`id`) USING BTREE,
              KEY `teams_creator_IDX` (`creator`) USING BTREE,
              KEY `teams_modifier_IDX` (`modifier`) USING BTREE,
              CONSTRAINT `teams_FK` FOREIGN KEY (`creator`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE '.
                'CASCADE,
              CONSTRAINT `teams_FK_1` FOREIGN KEY (`modifier`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE '.
                'CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');

        $this->addSql('CREATE TABLE `team_members` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `member` int unsigned NOT NULL,
              `team` int unsigned NOT NULL,
              `roles` json NOT NULL,
              `creator` int unsigned NOT NULL,
              `created` datetime NOT NULL,
              `modifier` int unsigned DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `team_members_UN` (`id`,`member`),
              KEY `team_members_id_IDX` (`id`) USING BTREE,
              KEY `team_members_member_IDX` (`member`) USING BTREE,
              KEY `team_members_creator_IDX` (`creator`) USING BTREE,
              KEY `team_members_modifier_IDX` (`modifier`) USING BTREE,
              KEY `team_members_FK_3` (`team`),
              CONSTRAINT `team_members_FK` FOREIGN KEY (`creator`) REFERENCES `users` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE,
              CONSTRAINT `team_members_FK_1` FOREIGN KEY (`modifier`) REFERENCES `users` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE,
              CONSTRAINT `team_members_FK_2` FOREIGN KEY (`member`) REFERENCES `users` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE,
              CONSTRAINT `team_members_FK_3` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');

        $this->addSql('CREATE TABLE `team_invitations` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `team` int unsigned NOT NULL,
              `email` varchar(250) NOT NULL,
              `token` varchar(250) NOT NULL,
              `creator` int unsigned NOT NULL,
              `created` datetime NOT NULL,
              `modifier` int unsigned DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `un_email_team` (`team`,`email`),
              KEY `team_invitations_team_IDX` (`team`) USING BTREE,
              KEY `team_invitations_creator_fk` (`creator`) USING BTREE,
              KEY `team_invitations_modifier_fk` (`modifier`) USING BTREE,
              CONSTRAINT `team_invitations_team_fk` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE CASCADE '.
                'ON UPDATE CASCADE,
              CONSTRAINT `team_invitations_creator_fk` FOREIGN KEY (`creator`) REFERENCES `users` (`id`) ON DELETE '.
                'CASCADE ON UPDATE CASCADE,
              CONSTRAINT `team_invitations_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `users` (`id`) ON DELETE '.
                'CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');

        $this->addSql('CREATE TABLE `board_teams` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `board` int unsigned NOT NULL,
              `team` int unsigned NOT NULL,
              `creator` int unsigned NOT NULL,
              `created` datetime NOT NULL,
              `modifier` int unsigned DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `board_teams_id_IDX` (`id`) USING BTREE,
              KEY `board_teams_creator_IDX` (`creator`) USING BTREE,
              KEY `board_teams_modifier_IDX` (`modifier`) USING BTREE,
              KEY `board_teams_board_IDX` (`board`,`team`) USING BTREE,
              KEY `board_teams_FK_1` (`team`),
              CONSTRAINT `board_teams_FK` FOREIGN KEY (`board`) REFERENCES `boards` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE,
              CONSTRAINT `board_teams_FK_1` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE,
              CONSTRAINT `board_teams_FK_2` FOREIGN KEY (`creator`) REFERENCES `users` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE,
              CONSTRAINT `board_teams_FK_3` FOREIGN KEY (`modifier`) REFERENCES `users` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE board_teans');
        $this->addSql('DROP TABLE team_invitations');
        $this->addSql('DROP TABLE team_members');
        $this->addSql('DROP TABLE teams');
    }
}
