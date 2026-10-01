<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261001130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move default catalogue media away from the public catalogue route.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("UPDATE catalog_page SET image_path = REPLACE(image_path, '/catalogue/default/', '/media/catalogue/default/') WHERE image_path LIKE '/catalogue/default/%'");
        $this->addSql("UPDATE catalog_page SET pdf_path = REPLACE(pdf_path, '/catalogue/default/', '/media/catalogue/default/') WHERE pdf_path LIKE '/catalogue/default/%'");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("UPDATE catalog_page SET image_path = REPLACE(image_path, '/media/catalogue/default/', '/catalogue/default/') WHERE image_path LIKE '/media/catalogue/default/%'");
        $this->addSql("UPDATE catalog_page SET pdf_path = REPLACE(pdf_path, '/media/catalogue/default/', '/catalogue/default/') WHERE pdf_path LIKE '/media/catalogue/default/%'");
    }
}
