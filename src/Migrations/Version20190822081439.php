<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190822081439 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE poll_entry (id INT AUTO_INCREMENT NOT NULL, `label` INT NOT NULL, count INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('INSERT INTO poll_entry (`label`, `count`) VALUES (1 , 0)');
        $this->addSql('INSERT INTO poll_entry (`label`, `count`) VALUES (2 , 0)');
        $this->addSql('INSERT INTO poll_entry (`label`, `count`) VALUES (3 , 0)');
        $this->addSql('INSERT INTO poll_entry (`label`, `count`) VALUES (4 , 0)');
        $this->addSql('INSERT INTO poll_entry (`label`, `count`) VALUES (5 , 0)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE poll_entry');
    }
}
