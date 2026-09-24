<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track anonymous visitor activity for the real-time admin audience dashboard.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("CREATE TABLE online_visitor (id INT AUTO_INCREMENT NOT NULL, visitor_key VARCHAR(64) NOT NULL, page_path VARCHAR(255) NOT NULL, page_title VARCHAR(180) NOT NULL, referrer_host VARCHAR(120) DEFAULT NULL, device VARCHAR(16) NOT NULL, browser VARCHAR(40) NOT NULL, page_views INT DEFAULT 1 NOT NULL, first_seen_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', last_seen_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_926486F02DB2DFEB (visitor_key), INDEX idx_online_visitor_last_seen (last_seen_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE online_visitor');
    }
}
