<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260621113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set user.establishment_id FK ON DELETE SET NULL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498565851');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498565851');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id)');
    }
}
