<?php

declare(strict_types=1);

namespace App\Tests\Blog;

use PHPUnit\Framework\TestCase;

final class LegacyBlogDataTest extends TestCase
{
    public function testImportedBlogDataIsCompleteAndUsesLocalMedia(): void
    {
        $projectDirectory = dirname(__DIR__, 2);
        $posts = require $projectDirectory.'/migrations/data/blog_posts.php';

        self::assertCount(12, $posts);
        self::assertCount(12, array_unique(array_column($posts, 'slug')));

        foreach ($posts as $post) {
            self::assertSame('Les Consultants', $post['author']);
            self::assertNotSame('', $post['content']);
            self::assertStringNotContainsString('wp-content', $post['content']);
            self::assertNotNull($post['featured_image']);
            self::assertFileExists($projectDirectory.'/assets/'.$post['featured_image']);

            foreach ($post['content_images'] as $image) {
                self::assertFileExists($projectDirectory.'/assets/'.$image['path']);
            }
        }
    }
}
