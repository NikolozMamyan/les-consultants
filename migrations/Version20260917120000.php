<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the blog and import the twelve unique legacy articles.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT NOT NULL, content LONGTEXT NOT NULL, category VARCHAR(80) NOT NULL, author VARCHAR(120) NOT NULL, featured_image VARCHAR(255) DEFAULT NULL, featured_image_alt VARCHAR(255) NOT NULL, content_images JSON NOT NULL, meta_title VARCHAR(255) NOT NULL, meta_description VARCHAR(255) NOT NULL, source_url VARCHAR(2048) NOT NULL, published TINYINT(1) NOT NULL, published_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX uniq_blog_post_slug (slug), INDEX idx_blog_post_published_at (published_at), INDEX idx_blog_post_category (category), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        /** @var list<array<string, mixed>> $posts */
        $posts = require __DIR__.'/data/blog_posts.php';

        foreach ($posts as $post) {
            $this->addSql(
                'INSERT INTO blog_post (title, slug, excerpt, content, category, author, featured_image, featured_image_alt, content_images, meta_title, meta_description, source_url, published, published_at, updated_at) VALUES (:title, :slug, :excerpt, :content, :category, :author, :featured_image, :featured_image_alt, :content_images, :meta_title, :meta_description, :source_url, :published, :published_at, :updated_at)',
                [
                    'title' => $post['title'],
                    'slug' => $post['slug'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'category' => $post['category'],
                    'author' => $post['author'],
                    'featured_image' => $post['featured_image'],
                    'featured_image_alt' => $post['featured_image_alt'],
                    'content_images' => json_encode($post['content_images'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'source_url' => $post['source_url'],
                    'published' => $post['published'],
                    'published_at' => $post['published_at'],
                    'updated_at' => $post['updated_at'],
                ],
                [
                    'published' => 'boolean',
                ],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE blog_post');
    }
}
