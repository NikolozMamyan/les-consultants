<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ElearningCommunityFeedClient
{
    private string $baseUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        #[Autowire('%env(ELEARNING_BASE_URL)%')]
        string $baseUrl,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 6): array
    {
        $limit = max(1, min(12, $limit));

        $cacheKey = 'elearning.community_feed.'.hash('xxh128', $this->baseUrl).'.'.$limit;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($limit): array {
            $item->expiresAfter(60);

            return $this->fetch($limit);
        });
    }

    /** @return list<array<string, mixed>> */
    private function fetch(int $limit): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->baseUrl.'/api/public/community-feed', [
                'query' => ['limit' => $limit],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 2.5,
            ]);

            if (200 !== $response->getStatusCode()) {
                return [];
            }

            $payload = $response->toArray(false);
        } catch (TransportExceptionInterface|DecodingExceptionInterface) {
            return [];
        }

        if (!isset($payload['items']) || !is_array($payload['items'])) {
            return [];
        }

        $items = [];
        foreach ($payload['items'] as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = $this->normalize($item);
            if (null !== $normalized) {
                $items[] = $normalized;
            }
        }

        return $items;
    }

    /** @param array<string, mixed> $item
     *  @return array<string, mixed>|null
     */
    private function normalize(array $item): ?array
    {
        $title = $this->text($item['title'] ?? null, 180);
        if ('' === $title) {
            return null;
        }

        $author = is_array($item['author'] ?? null) ? $item['author'] : [];
        $media = is_array($item['media'] ?? null) ? $item['media'] : null;
        $engagement = is_array($item['engagement'] ?? null) ? $item['engagement'] : [];
        $links = is_array($item['links'] ?? null) ? $item['links'] : [];
        $comments = [];

        foreach (is_array($item['latestComments'] ?? null) ? $item['latestComments'] : [] as $comment) {
            if (!is_array($comment)) {
                continue;
            }

            $content = $this->text($comment['content'] ?? null, 150);
            if ('' !== $content) {
                $comments[] = [
                    'author' => $this->text($comment['author'] ?? null, 80) ?: 'Membre',
                    'content' => $content,
                    'publishedAt' => $this->date($comment['publishedAt'] ?? null),
                    'likes' => max(0, (int) ($comment['likes'] ?? 0)),
                ];
            }
        }

        return [
            'platformId' => max(0, (int) ($item['id'] ?? 0)),
            'title' => $title,
            'excerpt' => $this->text($item['excerpt'] ?? null, 240),
            'content' => is_string($item['content'] ?? null) ? $item['content'] : '',
            'publishedAt' => $this->date($item['publishedAt'] ?? null),
            'author' => [
                'name' => $this->text($author['name'] ?? null, 80) ?: 'Membre de la communauté',
                'avatarUrl' => $this->absoluteUrl($author['avatarUrl'] ?? null),
            ],
            'media' => $media ? [
                'type' => in_array($media['type'] ?? null, ['image', 'video'], true) ? $media['type'] : 'image',
                'url' => $this->absoluteUrl($media['url'] ?? null),
            ] : null,
            'likesCount' => max(0, (int) ($engagement['likes'] ?? 0)),
            'commentsCount' => max(0, (int) ($engagement['comments'] ?? 0)),
            'latestComments' => array_slice($comments, 0, 6),
            'sourceUrl' => $this->absoluteUrl($links['article'] ?? null) ?? $this->baseUrl,
            'registrationUrl' => $this->absoluteUrl($links['register'] ?? null) ?? $this->baseUrl.'/register',
        ];
    }

    private function text(mixed $value, int $maximumLength): string
    {
        if (!is_string($value)) {
            return '';
        }

        $value = trim((string) preg_replace('/\s+/u', ' ', strip_tags($value)));

        return mb_substr($value, 0, $maximumLength);
    }

    private function date(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        try {
            return (new \DateTimeImmutable($value))->format(DATE_ATOM);
        } catch (\Exception) {
            return '';
        }
    }

    private function absoluteUrl(mixed $value): ?string
    {
        if (!is_string($value) || '' === $value) {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return str_starts_with($value, '/') ? $this->baseUrl.$value : null;
    }
}
