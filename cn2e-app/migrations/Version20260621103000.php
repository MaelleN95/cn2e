<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260621103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add academy column to establishment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE establishment ADD academy VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE establishment DROP academy');
    }
}
