<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260514153322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_program DROP FOREIGN KEY `FK_1763BD228565851`');
        $this->addSql('DROP INDEX IDX_1763BD228565851 ON academic_program');
        $this->addSql('ALTER TABLE academic_program DROP establishment_id');
        $this->addSql('ALTER TABLE event DROP time');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_program ADD establishment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE academic_program ADD CONSTRAINT `FK_1763BD228565851` FOREIGN KEY (establishment_id) REFERENCES establishment (id)');
        $this->addSql('CREATE INDEX IDX_1763BD228565851 ON academic_program (establishment_id)');
        $this->addSql('ALTER TABLE event ADD time VARCHAR(50) NOT NULL');
    }
}
