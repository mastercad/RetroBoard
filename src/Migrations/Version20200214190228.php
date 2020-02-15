<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214190228 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create Color and Avatar Path columns for User';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE `users` ADD `avatar_path` VARCHAR(250) NULL AFTER `activity_token`, ADD `color` VARCHAR(7) NULL AFTER `avatar_path`;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE `users` DROP `avatar_path`, DROP `color`;');
    }
}
