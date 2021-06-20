<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190831091936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create Database Schema';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'CREATE TABLE boards (id INT(11) unsigned AUTO_INCREMENT NOT NULL, creator INT(11) unsigned '.
            'DEFAULT NULL, modifier INT(11) unsigned DEFAULT NULL, name VARCHAR(250) NOT NULL, created DATETIME NOT '.
            'NULL, modified DATETIME DEFAULT NULL, INDEX boards_modifier_fk (modifier), '.
            'INDEX boards_creator_fk (creator), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE '.
            'utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE columns (id INT(11) unsigned AUTO_INCREMENT NOT NULL, board_fk INT(11) unsigned '.
            'DEFAULT NULL, name VARCHAR(20) NOT NULL, priority INT(11) DEFAULT NULL, '.
            'INDEX columns_board_fk (board_fk), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE '.
            'utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE tickets (id INT(11) unsigned AUTO_INCREMENT NOT NULL, column_fk INT(11) '.
            'unsigned DEFAULT NULL, creator INT(11) unsigned DEFAULT NULL, modifier INT(11) unsigned DEFAULT NULL, '.
            'content TEXT NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, is_archived TINYINT(1) '.
            'DEFAULT 0, INDEX tickets_modifier_fk (modifier), INDEX tickets_column_fk (column_fk), INDEX '.
            'tickets_creator_fk (creator), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci '.
            'ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE users (id INT(11) unsigned AUTO_INCREMENT NOT NULL, creator INT(11) unsigned '.
            'DEFAULT NULL, modifier INT(11) unsigned DEFAULT NULL, name VARCHAR(50) NOT NULL, password VARCHAR(250) '.
            'NOT NULL, email VARCHAR(250) NOT NULL, salt VARCHAR(250) DEFAULT NULL, activity_token VARCHAR(250) '.
            'DEFAULT NULL, roles JSON NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, INDEX '.
            'retro_modifier_fk (modifier), INDEX retro_creator_fk (creator), PRIMARY KEY(id)) DEFAULT CHARACTER SET '.
            'utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE votings (id INT(11) unsigned AUTO_INCREMENT NOT NULL, creator INT(11) unsigned '.
            'DEFAULT NULL, modifier INT(11) unsigned DEFAULT NULL, ticket_fk INT(11) unsigned DEFAULT NULL, points '.
            'INT(11) NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, INDEX votings_creator_fk '.
            '(creator), INDEX votings_ticket_fk (ticket_fk), INDEX votings_modifier_fk (modifier), PRIMARY KEY(id)) '.
            'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE boards ADD CONSTRAINT FK_F3EE4D13BC06EA63 FOREIGN KEY (creator) REFERENCES '.'users (id)'
        );
        $this->addSql(
            'ALTER TABLE boards ADD CONSTRAINT FK_F3EE4D13ABBFD9FD FOREIGN KEY (modifier) REFERENCES users (id)'
        );
        $this->addSql(
            'ALTER TABLE columns ADD CONSTRAINT FK_ACCEC0B7F0CB56DB FOREIGN KEY (board_fk) REFERENCES boards (id)'
        );
        $this->addSql(
            'ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4A9A98F8B FOREIGN KEY (column_fk) REFERENCES columns (id)'
        );
        $this->addSql(
            'ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4BC06EA63 FOREIGN KEY (creator) REFERENCES users (id)'
        );
        $this->addSql(
            'ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4ABBFD9FD FOREIGN KEY (modifier) REFERENCES users (id)'
        );
        $this->addSql(
            'ALTER TABLE users ADD CONSTRAINT FK_1483A5E9BC06EA63 FOREIGN KEY (creator) REFERENCES users (id)'
        );
        $this->addSql(
            'ALTER TABLE users ADD CONSTRAINT FK_1483A5E9ABBFD9FD FOREIGN KEY (modifier) REFERENCES users (id)'
        );
        $this->addSql(
            'ALTER TABLE votings ADD CONSTRAINT FK_F342AABC06EA63 FOREIGN KEY (creator) REFERENCES users (id)'
        );
        $this->addSql(
            'ALTER TABLE votings ADD CONSTRAINT FK_F342AAABBFD9FD FOREIGN KEY (modifier) REFERENCES users (id)'
        );
        $this->addSql(
            'ALTER TABLE votings ADD CONSTRAINT FK_F342AA6727468C FOREIGN KEY (ticket_fk) REFERENCES tickets (id)'
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE columns DROP FOREIGN KEY FK_ACCEC0B7F0CB56DB');
        $this->addSql('ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF4A9A98F8B');
        $this->addSql('ALTER TABLE votings DROP FOREIGN KEY FK_F342AA6727468C');
        $this->addSql('ALTER TABLE boards DROP FOREIGN KEY FK_F3EE4D13BC06EA63');
        $this->addSql('ALTER TABLE boards DROP FOREIGN KEY FK_F3EE4D13ABBFD9FD');
        $this->addSql('ALTER TABLE columns DROP FOREIGN KEY FK_ACCEC0B7BC06EA63');
        $this->addSql('ALTER TABLE columns DROP FOREIGN KEY FK_ACCEC0B7ABBFD9FD');
        $this->addSql('ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF4BC06EA63');
        $this->addSql('ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF4ABBFD9FD');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9BC06EA63');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9ABBFD9FD');
        $this->addSql('ALTER TABLE votings DROP FOREIGN KEY FK_F342AABC06EA63');
        $this->addSql('ALTER TABLE votings DROP FOREIGN KEY FK_F342AAABBFD9FD');
        $this->addSql('DROP TABLE boards');
        $this->addSql('DROP TABLE columns');
        $this->addSql('DROP TABLE tickets');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE votings');
    }
}
