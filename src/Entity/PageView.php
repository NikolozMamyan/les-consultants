<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PageViewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageViewRepository::class)]
#[ORM\Table(name: 'visitor_page_view')]
#[ORM\Index(name: 'idx_page_view_visited_at', columns: ['visited_at'])]
#[ORM\Index(name: 'idx_page_view_path_visited', columns: ['page_path', 'visited_at'])]
class PageView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private string $visitorKey;

    #[ORM\Column(length: 255)]
    private string $pagePath;

    #[ORM\Column(length: 180)]
    private string $pageTitle;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $referrerHost;

    #[ORM\Column(length: 16)]
    private string $device;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $visitedAt;

    public function __construct(
        string $visitorKey,
        string $pagePath,
        string $pageTitle,
        string $device,
        ?string $referrerHost,
        ?\DateTimeImmutable $visitedAt = null,
    ) {
        $this->visitorKey = $visitorKey;
        $this->pagePath = $pagePath;
        $this->pageTitle = $pageTitle;
        $this->device = $device;
        $this->referrerHost = $referrerHost;
        $this->visitedAt = $visitedAt ?? new \DateTimeImmutable();
    }
}
