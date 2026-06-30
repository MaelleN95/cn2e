<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add configurable sender email to site information';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_information ADD sender_email VARCHAR(180) DEFAULT NULL');
        $this->addSql("UPDATE site_information SET sender_email = 'contact@koji-dev.fr' WHERE sender_email = '' OR sender_email IS NULL");
        $this->addSql("ALTER TABLE site_information MODIFY sender_email VARCHAR(180) NOT NULL DEFAULT 'contact@koji-dev.fr'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_information DROP sender_email');
    }
}
