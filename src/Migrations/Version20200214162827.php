<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214162827 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add Board Subscribers Table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `board_subscribers` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `board` int(11) unsigned NOT NULL,
            `subscriber` int(11) unsigned NOT NULL,
            `creator` int(11) unsigned NOT NULL,
            `created` datetime NOT NULL,
            `modifier` int(11) unsigned DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `un_board_subscriber` (`board`,`subscriber`),
            KEY `board_subscriber_subscriber` (`subscriber`),
            KEY `board_subscriber_creator` (`creator`),
            KEY `board_subscriber_modifier` (`modifier`),
            CONSTRAINT `board_subscriber_board` FOREIGN KEY (`board`) REFERENCES `boards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `board_subscriber_creator` FOREIGN KEY (`creator`) REFERENCES `users` (`id`),
            CONSTRAINT `board_subscriber_modifier` FOREIGN KEY (`modifier`) REFERENCES `users` (`id`),
            CONSTRAINT `board_subscriber_subscriber` FOREIGN KEY (`subscriber`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE board_subscribers');
    }
}
