<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OnlineVisitorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OnlineVisitorRepository::class)]
#[ORM\Table(name: 'online_visitor')]
#[ORM\Index(name: 'idx_online_visitor_last_seen', columns: ['last_seen_at'])]
class OnlineVisitor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $visitorKey;

    #[ORM\Column(length: 255)]
    private string $pagePath;

    #[ORM\Column(length: 180)]
    private string $pageTitle;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $referrerHost = null;

    #[ORM\Column(length: 16)]
    private string $device;

    #[ORM\Column(length: 40)]
    private string $browser;

    #[ORM\Column(options: ['default' => 1])]
    private int $pageViews = 1;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $firstSeenAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $lastSeenAt;

    public function __construct(
        string $visitorKey,
        string $pagePath,
        string $pageTitle,
        string $device,
        string $browser,
        ?string $referrerHost,
    ) {
        $this->visitorKey = $visitorKey;
        $this->pagePath = $pagePath;
        $this->pageTitle = $pageTitle;
        $this->device = $device;
        $this->browser = $browser;
        $this->referrerHost = $referrerHost;
        $this->firstSeenAt = new \DateTimeImmutable();
        $this->lastSeenAt = $this->firstSeenAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitorKey(): string
    {
        return $this->visitorKey;
    }

    public function getPagePath(): string
    {
        return $this->pagePath;
    }

    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    public function getReferrerHost(): ?string
    {
        return $this->referrerHost;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getPageViews(): int
    {
        return $this->pageViews;
    }

    public function getFirstSeenAt(): \DateTimeImmutable
    {
        return $this->firstSeenAt;
    }

    public function getLastSeenAt(): \DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function recordActivity(
        string $pagePath,
        string $pageTitle,
        string $device,
        string $browser,
        ?string $referrerHost,
        \DateTimeImmutable $now,
    ): bool {
        $sessionRestarted = $this->lastSeenAt < $now->modify('-30 minutes');
        $pageChanged = $sessionRestarted || $pagePath !== $this->pagePath;

        if ($sessionRestarted) {
            $this->firstSeenAt = $now;
            $this->pageViews = 1;
            $this->referrerHost = $referrerHost;
        } elseif ($pageChanged) {
            ++$this->pageViews;
        }

        $this->pagePath = $pagePath;
        $this->pageTitle = $pageTitle;
        $this->device = $device;
        $this->browser = $browser;
        $this->lastSeenAt = $now;

        return $pageChanged;
    }
}
