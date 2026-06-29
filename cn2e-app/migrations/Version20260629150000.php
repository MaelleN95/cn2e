<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add social network links to site information';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_information ADD linkedin_url VARCHAR(255) DEFAULT NULL, ADD instagram_url VARCHAR(255) DEFAULT NULL, ADD facebook_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_information DROP linkedin_url, DROP instagram_url, DROP facebook_url');
    }
}
