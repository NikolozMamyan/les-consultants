<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Persist editable theme page content and carousel settings.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("CREATE TABLE theme_page (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(40) NOT NULL, content JSON NOT NULL, carousels JSON NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX uniq_theme_page_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE theme_page');
    }
}
