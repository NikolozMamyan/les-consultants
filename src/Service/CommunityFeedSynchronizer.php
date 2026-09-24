<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CommunityPost;
use App\Repository\CommunityPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class CommunityFeedSynchronizer
{
    public function __construct(
        private ElearningCommunityFeedClient $client,
        private CommunityPostRepository $posts,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private CacheInterface $cache,
    ) {
    }

    public function synchronizeIfDue(): int
    {
        return $this->cache->get('community_feed.database_sync', function (ItemInterface $item): int {
            $item->expiresAfter(60);

            return $this->synchronize();
        });
    }

    public function synchronize(): int
    {
        $items = $this->client->latest(12);
        if ([] === $items) {
            return 0;
        }

        $synchronized = 0;
        foreach ($items as $item) {
            $platformId = (int) ($item['platformId'] ?? 0);
            if ($platformId <= 0) {
                continue;
            }

            $post = $this->posts->findOneBy(['platformId' => $platformId]);
            if (!$post instanceof CommunityPost) {
                $post = (new CommunityPost())
                    ->setPlatformId($platformId)
                    ->setSlug($this->slug((string) $item['title'], $platformId));
            }

            $media = is_array($item['media'] ?? null) ? $item['media'] : [];
            $post
                ->setTitle((string) $item['title'])
                ->setExcerpt((string) $item['excerpt'])
                ->setContent((string) ($item['content'] ?: $item['excerpt']))
                ->setAuthorName((string) $item['author']['name'])
                ->setAuthorAvatarUrl($item['author']['avatarUrl'] ?? null)
                ->setMediaType($media['type'] ?? null)
                ->setMediaUrl($media['url'] ?? null)
                ->setLikesCount((int) $item['likesCount'])
                ->setCommentsCount((int) $item['commentsCount'])
                ->setLatestComments($item['latestComments'])
                ->setSourceUrl((string) $item['sourceUrl'])
                ->setRegistrationUrl((string) $item['registrationUrl'])
                ->setPublishedAt(new \DateTimeImmutable((string) $item['publishedAt']))
                ->markSynced();

            $this->entityManager->persist($post);
            ++$synchronized;
        }

        $this->entityManager->flush();

        return $synchronized;
    }

    private function slug(string $title, int $platformId): string
    {
        $slug = strtolower((string) $this->slugger->slug($title));

        return ($slug ?: 'publication').'-'.$platformId;
    }
}
