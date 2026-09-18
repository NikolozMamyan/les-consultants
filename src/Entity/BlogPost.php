<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ORM\Table(name: 'blog_post')]
#[ORM\UniqueConstraint(name: 'uniq_blog_post_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_blog_post_published_at', columns: ['published_at'])]
#[ORM\Index(name: 'idx_blog_post_category', columns: ['category'])]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT)]
    private string $excerpt;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(length: 80)]
    private string $category;

    #[ORM\Column(length: 120)]
    private string $author = 'Les Consultants';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featuredImage = null;

    #[ORM\Column(length: 255)]
    private string $featuredImageAlt;

    /** @var list<array{path: string, alt: string}> */
    #[ORM\Column(type: Types::JSON)]
    private array $contentImages = [];

    #[ORM\Column(length: 255)]
    private string $metaTitle;

    #[ORM\Column(length: 255)]
    private string $metaDescription;

    #[ORM\Column(length: 2048)]
    private string $sourceUrl;

    #[ORM\Column]
    private bool $published = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function getFeaturedImageAlt(): string
    {
        return $this->featuredImageAlt;
    }

    /** @return list<array{path: string, alt: string}> */
    public function getContentImages(): array
    {
        return $this->contentImages;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getReadingTime(): int
    {
        $words = count(preg_split('/\s+/u', trim(strip_tags($this->content)), -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($words / 220));
    }
}
