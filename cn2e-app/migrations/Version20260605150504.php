<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260605150504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, pdf_name VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, article_id INT NOT NULL, INDEX IDX_D8698A767294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A767294869C FOREIGN KEY (article_id) REFERENCES article (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A767294869C');
        $this->addSql('DROP TABLE document');
    }
}
