<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ElearningCommunityFeedClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ElearningCommunityFeedClientTest extends TestCase
{
    public function testItNormalizesThePublicFeedAndResolvesPlatformUrls(): void
    {
        $httpClient = new MockHttpClient(new MockResponse(json_encode([
            'items' => [[
                'id' => 42,
                'title' => 'Veille réglementaire AML',
                'excerpt' => '<p>Les points à retenir cette semaine.</p>',
                'content' => '<p>Le contenu complet de la publication.</p>',
                'publishedAt' => '2026-09-24T10:00:00+02:00',
                'author' => ['name' => 'Sophie Martin', 'avatarUrl' => '/uploads/avatars/sophie.jpg'],
                'media' => ['type' => 'image', 'url' => '/uploads/articles/aml.jpg'],
                'engagement' => ['likes' => 18, 'comments' => 4],
                'latestComments' => [['author' => 'Marc', 'content' => '<b>Très utile</b> pour nos équipes.']],
                'links' => ['register' => '/register'],
            ]],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $client = new ElearningCommunityFeedClient($httpClient, new ArrayAdapter(), 'https://academy.example');
        $items = $client->latest(6);

        self::assertCount(1, $items);
        self::assertSame(42, $items[0]['platformId']);
        self::assertSame('<p>Le contenu complet de la publication.</p>', $items[0]['content']);
        self::assertSame('https://academy.example/register', $items[0]['registrationUrl']);
        self::assertSame('https://academy.example', $items[0]['sourceUrl']);
        self::assertSame('https://academy.example/uploads/articles/aml.jpg', $items[0]['media']['url']);
        self::assertSame('Les points à retenir cette semaine.', $items[0]['excerpt']);
        self::assertSame(18, $items[0]['likesCount']);
        self::assertSame('Très utile pour nos équipes.', $items[0]['latestComments'][0]['content']);
    }

    public function testItKeepsTheBlogAvailableWhenThePlatformIsUnavailable(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('', ['http_code' => 503]));
        $client = new ElearningCommunityFeedClient($httpClient, new ArrayAdapter(), 'https://academy.example');

        self::assertSame([], $client->latest());
    }
}
