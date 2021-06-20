<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200214164444 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create Demo Board';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `boards` (`id`, `name`, `creator`, `created`, `modifier`, `modified`) VALUES (1, ".
            "'Demo Board', 1, '2019-09-05 21:50:57', NULL, NULL);");

        $this->addSql("INSERT INTO `columns` (`id`, `name`, `priority`, `board_fk`) VALUES (1, 'What worked', 0, 1), ".
            "(2, 'What didn\'t work', 1, 1), (3, 'Ideas', 2, 1), (4, 'Appreciations', 3, 1);");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DELETE boards FROM boards WHERE name LIKE("Demo Board");');
    }
}
