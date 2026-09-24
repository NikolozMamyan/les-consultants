<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store anonymous page views for dynamic dashboard traffic metrics.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("CREATE TABLE visitor_page_view (id INT AUTO_INCREMENT NOT NULL, visitor_key VARCHAR(64) NOT NULL, page_path VARCHAR(255) NOT NULL, page_title VARCHAR(180) NOT NULL, referrer_host VARCHAR(120) DEFAULT NULL, device VARCHAR(16) NOT NULL, visited_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_page_view_visited_at (visited_at), INDEX idx_page_view_path_visited (page_path, visited_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE visitor_page_view');
    }
}
