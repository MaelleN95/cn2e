<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260514153526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE academic_program_establishment (academic_program_id INT NOT NULL, establishment_id INT NOT NULL, INDEX IDX_608B8A43D858B7BE (academic_program_id), INDEX IDX_608B8A438565851 (establishment_id), PRIMARY KEY (academic_program_id, establishment_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE academic_program_establishment ADD CONSTRAINT FK_608B8A43D858B7BE FOREIGN KEY (academic_program_id) REFERENCES academic_program (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE academic_program_establishment ADD CONSTRAINT FK_608B8A438565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_program_establishment DROP FOREIGN KEY FK_608B8A43D858B7BE');
        $this->addSql('ALTER TABLE academic_program_establishment DROP FOREIGN KEY FK_608B8A438565851');
        $this->addSql('DROP TABLE academic_program_establishment');
    }
}
