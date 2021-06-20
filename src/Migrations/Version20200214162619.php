<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214162619 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add Board Members Table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('CREATE TABLE `board_members` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user` int(11) unsigned NOT NULL,
            `roles` json NOT NULL,
            `board` int(11) unsigned NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `created` datetime NOT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `un_board_user` (`board`,`user`),
            KEY `board_member_user_fk` (`user`),
            KEY `board_member_creator_fk` (`creator`),
            KEY `board_member_modifier_fk` (`modifier`),
            CONSTRAINT `board_member_board_fk` FOREIGN KEY (`board`) REFERENCES `boards` (`id`) ON DELETE CASCADE '.
                'ON UPDATE CASCADE,
            CONSTRAINT `board_member_creator_fk` FOREIGN KEY (`creator`) REFERENCES `users` (`id`) ON DELETE CASCADE '.
                'ON UPDATE CASCADE,
            CONSTRAINT `board_member_modifier_fk` FOREIGN KEY (`modifier`) REFERENCES `users` (`id`) ON DELETE '.
                'CASCADE ON UPDATE CASCADE,
            CONSTRAINT `board_member_user_fk` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON '.
                'UPDATE CASCADE
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('DROP TABLE board_members');
    }
}
