<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommunityPostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommunityPostRepository::class)]
#[ORM\Table(name: 'community_post')]
#[ORM\UniqueConstraint(name: 'uniq_community_post_platform_id', columns: ['platform_id'])]
#[ORM\UniqueConstraint(name: 'uniq_community_post_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_community_post_published_at', columns: ['published_at'])]
class CommunityPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $platformId;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $excerpt = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\Column(length: 120)]
    private string $authorName = '';

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $authorAvatarUrl = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $mediaType = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $mediaUrl = null;

    #[ORM\Column]
    private int $likesCount = 0;

    #[ORM\Column]
    private int $commentsCount = 0;

    /** @var list<array{author: string, content: string, publishedAt: string, likes: int}> */
    #[ORM\Column(type: Types::JSON)]
    private array $latestComments = [];

    #[ORM\Column(length: 500)]
    private string $sourceUrl = '';

    #[ORM\Column(length: 500)]
    private string $registrationUrl = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $syncedAt;

    public function __construct()
    {
        $this->publishedAt = new \DateTimeImmutable();
        $this->syncedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getPlatformId(): int { return $this->platformId; }
    public function setPlatformId(int $platformId): self { $this->platformId = $platformId; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = trim($title); return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = trim($slug); return $this; }
    public function getExcerpt(): string { return $this->excerpt; }
    public function setExcerpt(string $excerpt): self { $this->excerpt = trim($excerpt); return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = trim($content); return $this; }
    public function getAuthorName(): string { return $this->authorName; }
    public function setAuthorName(string $authorName): self { $this->authorName = trim($authorName); return $this; }
    public function getAuthorAvatarUrl(): ?string { return $this->authorAvatarUrl; }
    public function setAuthorAvatarUrl(?string $authorAvatarUrl): self { $this->authorAvatarUrl = $authorAvatarUrl; return $this; }
    public function getMediaType(): ?string { return $this->mediaType; }
    public function setMediaType(?string $mediaType): self { $this->mediaType = $mediaType; return $this; }
    public function getMediaUrl(): ?string { return $this->mediaUrl; }
    public function setMediaUrl(?string $mediaUrl): self { $this->mediaUrl = $mediaUrl; return $this; }
    public function getLikesCount(): int { return $this->likesCount; }
    public function setLikesCount(int $likesCount): self { $this->likesCount = max(0, $likesCount); return $this; }
    public function getCommentsCount(): int { return $this->commentsCount; }
    public function setCommentsCount(int $commentsCount): self { $this->commentsCount = max(0, $commentsCount); return $this; }
    /** @return list<array{author: string, content: string, publishedAt: string, likes: int}> */
    public function getLatestComments(): array { return $this->latestComments; }
    /** @param list<array{author: string, content: string, publishedAt: string, likes: int}> $comments */
    public function setLatestComments(array $comments): self { $this->latestComments = array_values($comments); return $this; }
    public function getSourceUrl(): string { return $this->sourceUrl; }
    public function setSourceUrl(string $sourceUrl): self { $this->sourceUrl = $sourceUrl; return $this; }
    public function getRegistrationUrl(): string { return $this->registrationUrl; }
    public function setRegistrationUrl(string $registrationUrl): self { $this->registrationUrl = $registrationUrl; return $this; }
    public function getPublishedAt(): \DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(\DateTimeImmutable $publishedAt): self { $this->publishedAt = $publishedAt; return $this; }
    public function getSyncedAt(): \DateTimeImmutable { return $this->syncedAt; }
    public function markSynced(): self { $this->syncedAt = new \DateTimeImmutable(); return $this; }
}
