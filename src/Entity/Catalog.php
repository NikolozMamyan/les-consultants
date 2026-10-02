<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CatalogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogRepository::class)]
#[ORM\Table(name: 'catalog')]
class Catalog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $title = 'Catalogue 2026';

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(length: 100)]
    private string $contentsTitle = 'Sommaire';

    /** @var array<string, string> */
    #[ORM\Column(type: 'json')]
    private array $contentsLabels = [];

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $contentsOrder = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getContentsTitle(): string
    {
        return $this->contentsTitle;
    }

    public function setContentsTitle(string $contentsTitle): self
    {
        $this->contentsTitle = trim($contentsTitle);

        return $this;
    }

    /** @return array<string, string> */
    public function getContentsLabels(): array
    {
        return $this->contentsLabels;
    }

    /** @param array<string, string> $contentsLabels */
    public function setContentsLabels(array $contentsLabels): self
    {
        $this->contentsLabels = $contentsLabels;

        return $this;
    }

    /** @return list<string> */
    public function getContentsOrder(): array
    {
        return $this->contentsOrder;
    }

    /** @param list<string> $contentsOrder */
    public function setContentsOrder(array $contentsOrder): self
    {
        $this->contentsOrder = array_values($contentsOrder);

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
}
