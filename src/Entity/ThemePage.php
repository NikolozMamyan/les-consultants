<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ThemePageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemePageRepository::class)]
#[ORM\Table(name: 'theme_page')]
#[ORM\UniqueConstraint(name: 'uniq_theme_page_slug', columns: ['slug'])]
class ThemePage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private string $slug;

    /** @var array<string, string> */
    #[ORM\Column(type: Types::JSON)]
    private array $content = [];

    /** @var array<string, array{interval: int, mode: string}> */
    #[ORM\Column(type: Types::JSON)]
    private array $carousels = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /** @return array<string, string> */
    public function getContent(): array
    {
        return $this->content;
    }

    /** @param array<string, string> $content */
    public function setContent(array $content): void
    {
        $this->content = $content;
    }

    /** @return array<string, array{interval: int, mode: string}> */
    public function getCarousels(): array
    {
        return $this->carousels;
    }

    /** @param array<string, array{interval: int, mode: string}> $carousels */
    public function setCarousels(array $carousels): void
    {
        $this->carousels = $carousels;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
