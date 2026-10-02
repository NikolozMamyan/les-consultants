<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Persist the custom order of catalogue contents entries.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('ALTER TABLE catalog ADD contents_order JSON DEFAULT NULL');
        $this->addSql('UPDATE catalog SET contents_order = JSON_ARRAY() WHERE contents_order IS NULL');
        $this->addSql('ALTER TABLE catalog MODIFY contents_order JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('ALTER TABLE catalog DROP contents_order');
    }
}
