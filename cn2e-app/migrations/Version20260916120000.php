<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916120000 extends AbstractMigration
{
    private const TABLES = ['article', 'establishment', 'event'];

    public function getDescription(): string
    {
        return 'Deduplicate slugs and add unique constraint on article, establishment and event slug columns';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table) {
            $this->deduplicateSlugs($table);
        }

        $this->addSql('ALTER TABLE article ADD UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug)');
        $this->addSql('ALTER TABLE establishment ADD UNIQUE INDEX UNIQ_DBEFB1EE989D9B62 (slug)');
        $this->addSql('ALTER TABLE event ADD UNIQUE INDEX UNIQ_3BAE0AA7989D9B62 (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP INDEX UNIQ_23A0E66989D9B62');
        $this->addSql('ALTER TABLE establishment DROP INDEX UNIQ_DBEFB1EE989D9B62');
        $this->addSql('ALTER TABLE event DROP INDEX UNIQ_3BAE0AA7989D9B62');
    }

    private function deduplicateSlugs(string $table): void
    {
        $rows = $this->connection->fetchAllAssociative(
            sprintf('SELECT id, slug FROM %s ORDER BY id ASC', $table)
        );

        $seen = [];

        foreach ($rows as $row) {
            $slug = $row['slug'];
            $id = $row['id'];

            if (!isset($seen[$slug])) {
                $seen[$slug] = true;
                continue;
            }

            $suffix = 2;
            $newSlug = $slug . '-' . $suffix;

            while (isset($seen[$newSlug])) {
                $suffix++;
                $newSlug = $slug . '-' . $suffix;
            }

            $seen[$newSlug] = true;

            $this->connection->executeStatement(
                sprintf('UPDATE %s SET slug = ? WHERE id = ?', $table),
                [$newSlug, $id]
            );
        }
    }
}
