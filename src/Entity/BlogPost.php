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
    private string $title = '';

    #[ORM\Column(length: 255)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $excerpt = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\Column(length: 80)]
    private string $category = '';

    #[ORM\Column(length: 120)]
    private string $author = 'Les Consultants';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featuredImage = null;

    #[ORM\Column(length: 255)]
    private string $featuredImageAlt = '';

    /** @var list<array{path: string, alt: string}> */
    #[ORM\Column(type: Types::JSON)]
    private array $contentImages = [];

    #[ORM\Column(length: 255)]
    private string $metaTitle = '';

    #[ORM\Column(length: 255)]
    private string $metaDescription = '';

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->publishedAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = trim($slug);

        return $this;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): self
    {
        $this->excerpt = trim($excerpt);

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = trim($content);

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = trim($category);

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = trim($author);

        return $this;
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?string $featuredImage): self
    {
        $this->featuredImage = $this->nullable($featuredImage);

        return $this;
    }

    public function getFeaturedImageAlt(): string
    {
        return $this->featuredImageAlt;
    }

    public function setFeaturedImageAlt(string $featuredImageAlt): self
    {
        $this->featuredImageAlt = trim($featuredImageAlt);

        return $this;
    }

    /** @return list<array{path: string, alt: string}> */
    public function getContentImages(): array
    {
        return $this->contentImages;
    }

    /** @param list<array{path: string, alt: string}> $contentImages */
    public function setContentImages(array $contentImages): self
    {
        $this->contentImages = array_values($contentImages);

        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = trim($metaTitle);

        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = trim($metaDescription);

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getReadingTime(): int
    {
        $words = count(preg_split('/\s+/u', trim(strip_tags($this->content)), -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($words / 220));
    }

    private function nullable(?string $value): ?string
    {
        $value = null === $value ? '' : trim($value);

        return '' === $value ? null : $value;
    }
}
