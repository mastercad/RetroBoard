<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190831092002 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add System User';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
            "INSERT INTO `users` (`id`, `name`, `password`, `email`, `salt`, `activity_token`, `roles`, `created`, `creator`, `modified`, `modifier`) ".
            "VALUES (1, 'SYSTEM', '293cbe3e337ee8499b127d2244cb230799fe82b0d0d308258b', '', NULL, NULL, '[\"ROLE_SUPERADMIN\"]', '2019-07-26 15:39:00', 1, NULL, NULL); ".
            "COMMIT;"
        );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DELETE users FROM users WHERE name = "SYSTEM"');
    }
}
