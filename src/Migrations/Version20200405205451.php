<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200405205451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add additional columns for social connect via google, microsoft and github';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `users` ADD `google_id` VARCHAR(250) NULL AFTER `email`;');
        $this->addSql('ALTER TABLE `users` ADD `github_id` VARCHAR(250) NULL AFTER `google_id`;');
        $this->addSql('ALTER TABLE `users` ADD `microsoft_id` VARCHAR(250) NULL AFTER `github_id`;');
        $this->addSql('ALTER TABLE `users` ADD `okta_id` VARCHAR(250) NULL AFTER `microsoft_id`;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `users` DROP `github_id`;');
        $this->addSql('ALTER TABLE `users` DROP `google_id`;');
        $this->addSql('ALTER TABLE `users` DROP `microsoft_id`;');
        $this->addSql('ALTER TABLE `users` DROP `okta_id`;');
    }
}
