<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CatalogPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogPageRepository::class)]
#[ORM\Table(name: 'catalog_page')]
#[ORM\Index(name: 'idx_catalog_page_position', columns: ['catalog_id', 'position_index'])]
class CatalogPage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Catalog $catalog;

    #[ORM\Column(name: 'position_index')]
    private int $position;

    #[ORM\Column(length: 180)]
    private string $title = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column(nullable: true)]
    private ?int $pdfPage = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $linkUrl = null;

    #[ORM\Column]
    private bool $openLinkInNewTab = false;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Catalog $catalog, int $position)
    {
        $this->catalog = $catalog;
        $this->position = $position;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCatalog(): Catalog
    {
        return $this->catalog;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
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

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $this->nullable($imagePath);

        return $this;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $pdfPath): self
    {
        $this->pdfPath = $this->nullable($pdfPath);

        return $this;
    }

    public function getPdfPage(): ?int
    {
        return $this->pdfPage;
    }

    public function setPdfPage(?int $pdfPage): self
    {
        $this->pdfPage = $pdfPage;

        return $this;
    }

    public function getLinkUrl(): ?string
    {
        return $this->linkUrl;
    }

    public function setLinkUrl(?string $linkUrl): self
    {
        $this->linkUrl = $this->nullable($linkUrl);

        return $this;
    }

    public function opensLinkInNewTab(): bool
    {
        return $this->openLinkInNewTab;
    }

    public function setOpenLinkInNewTab(bool $openLinkInNewTab): self
    {
        $this->openLinkInNewTab = $openLinkInNewTab;

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

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function nullable(?string $value): ?string
    {
        $value = null === $value ? '' : trim($value);

        return '' === $value ? null : $value;
    }
}
