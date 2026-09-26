<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\BlogPostRepository;
use App\Repository\CommunityPostRepository;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class SitemapProvider
{
    public function __construct(
        private BlogPostRepository $blogPosts,
        private CommunityPostRepository $communityPosts,
        private UrlGeneratorInterface $urlGenerator,
        private Packages $assets,
    ) {
    }

    /**
     * @return array{
     *     entries: list<array{location: string, lastModified: ?\DateTimeImmutable, image: ?string}>,
     *     lastModified: ?\DateTimeImmutable
     * }
     */
    public function provide(): array
    {
        $contentEntries = [];
        $latestModification = null;

        foreach ($this->blogPosts->findPublished() as $post) {
            $lastModified = $post->getUpdatedAt();
            $latestModification = $this->latest($latestModification, $lastModified);
            $contentEntries[] = $this->entry(
                'app_blog_show',
                ['slug' => $post->getSlug()],
                $lastModified,
                $this->absoluteAsset($post->getFeaturedImage()),
            );
        }

        foreach ($this->communityPosts->findForSitemap() as $post) {
            $lastModified = $post->getPublishedAt();
            $latestModification = $this->latest($latestModification, $lastModified);
            $contentEntries[] = $this->entry(
                'app_community_post_show',
                ['slug' => $post->getSlug()],
                $lastModified,
                'image' === $post->getMediaType() ? $post->getMediaUrl() : null,
            );
        }

        $entries = [];
        foreach (['app_home', 'app_expertises', 'app_cabinet', 'app_contact', 'app_deposit', 'app_legal_notice', 'app_privacy_policy'] as $route) {
            $entries[] = $this->entry($route);
        }
        $entries[] = $this->entry('app_blog_index', [], $latestModification);
        $entries = [...$entries, ...$contentEntries];

        return ['entries' => $entries, 'lastModified' => $latestModification];
    }

    /**
     * @param array<string, scalar> $parameters
     *
     * @return array{location: string, lastModified: ?\DateTimeImmutable, image: ?string}
     */
    private function entry(
        string $route,
        array $parameters = [],
        ?\DateTimeImmutable $lastModified = null,
        ?string $image = null,
    ): array {
        return [
            'location' => $this->urlGenerator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL),
            'lastModified' => $lastModified,
            'image' => $image,
        ];
    }

    private function absoluteAsset(?string $path): ?string
    {
        if (null === $path || '' === trim($path)) {
            return null;
        }

        $assetUrl = $this->assets->getUrl($path);
        if (str_starts_with($assetUrl, 'http://') || str_starts_with($assetUrl, 'https://')) {
            return $assetUrl;
        }

        $siteUrl = rtrim($this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL), '/');

        return $siteUrl.'/'.ltrim($assetUrl, '/');
    }

    private function latest(?\DateTimeImmutable $current, \DateTimeImmutable $candidate): \DateTimeImmutable
    {
        return null === $current || $candidate > $current ? $candidate : $current;
    }
}
