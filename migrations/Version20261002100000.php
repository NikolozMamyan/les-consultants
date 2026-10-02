<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the generated and manually editable catalogue contents page.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("ALTER TABLE catalog ADD contents_title VARCHAR(100) DEFAULT 'Sommaire' NOT NULL, ADD contents_labels JSON DEFAULT NULL");
        $this->addSql('UPDATE catalog SET contents_labels = JSON_OBJECT() WHERE contents_labels IS NULL');
        $this->addSql('ALTER TABLE catalog MODIFY contents_labels JSON NOT NULL');
        $this->addSql('ALTER TABLE catalog_page ADD pdf_original_name VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE catalog_page SET pdf_original_name = CONCAT(SUBSTRING_INDEX(title, ' · page ', 1), '.pdf') WHERE pdf_path IS NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('ALTER TABLE catalog DROP contents_title, DROP contents_labels');
        $this->addSql('ALTER TABLE catalog_page DROP pdf_original_name');
    }
}
