<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store synchronized e-learning community posts and engagement for local SEO pages.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("CREATE TABLE community_post (id INT AUTO_INCREMENT NOT NULL, platform_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT NOT NULL, content LONGTEXT NOT NULL, author_name VARCHAR(120) NOT NULL, author_avatar_url VARCHAR(500) DEFAULT NULL, media_type VARCHAR(20) DEFAULT NULL, media_url VARCHAR(500) DEFAULT NULL, likes_count INT NOT NULL, comments_count INT NOT NULL, latest_comments JSON NOT NULL, source_url VARCHAR(500) NOT NULL, registration_url VARCHAR(500) NOT NULL, published_at DATETIME NOT NULL, synced_at DATETIME NOT NULL, UNIQUE INDEX uniq_community_post_platform_id (platform_id), UNIQUE INDEX uniq_community_post_slug (slug), INDEX idx_community_post_published_at (published_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE community_post');
    }
}
