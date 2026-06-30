<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create singleton CN2E site information';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE site_information (id INT AUTO_INCREMENT NOT NULL, organization_name VARCHAR(255) NOT NULL, acronym VARCHAR(50) DEFAULT NULL, postal_address_line1 VARCHAR(255) NOT NULL, postal_address_line2 VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, contact_email VARCHAR(180) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO site_information (id, organization_name, acronym, postal_address_line1, postal_address_line2, postal_code, city, country, contact_email) VALUES (1, 'Comité National des EREA/LEA et ERPD', 'CN2E', '29 Rue de Cronstadt', NULL, '75015', 'Paris', 'France', 'secretariat.cn2e@gmail.com')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE site_information');
    }
}