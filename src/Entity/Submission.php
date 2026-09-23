<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubmissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[ORM\Table(name: 'lead_submission')]
#[ORM\Index(name: 'idx_submission_type_created_at', columns: ['type', 'created_at'])]
#[ORM\Index(name: 'idx_submission_status', columns: ['status'])]
class Submission
{
    public const TYPE_MISSION = 'mission';
    public const TYPE_PROFILE = 'profile';

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_CONTACTED,
        self::STATUS_CLOSED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    private string $type;

    #[ORM\Column(length: 160)]
    private string $subject;

    #[ORM\Column(length: 160)]
    private string $contactName;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $organization = null;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 35, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $expertise = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $details;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(length: 24)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $type, string $subject, string $contactName, string $email, string $details)
    {
        if (!in_array($type, [self::TYPE_MISSION, self::TYPE_PROFILE], true)) {
            throw new \InvalidArgumentException('Type de dépôt invalide.');
        }

        $this->type = $type;
        $this->subject = trim($subject);
        $this->contactName = trim($contactName);
        $this->email = mb_strtolower(trim($email));
        $this->details = trim($details);
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeLabel(): string
    {
        return self::TYPE_MISSION === $this->type ? 'Mission' : 'Profil';
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(?string $organization): self
    {
        $this->organization = $this->nullable($organization);

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $this->nullable($phone);

        return $this;
    }

    public function getExpertise(): ?string
    {
        return $this->expertise;
    }

    public function setExpertise(?string $expertise): self
    {
        $this->expertise = $this->nullable($expertise);

        return $this;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $this->nullable($duration);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => self::TYPE_PROFILE === $this->type ? 'À qualifier' : 'Nouveau',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_CONTACTED => 'Contacté',
            self::STATUS_CLOSED => 'Archivé',
            default => $this->status,
        };
    }

    public function getStatusTone(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => self::TYPE_PROFILE === $this->type ? 'a-qualifier' : 'nouveau',
            self::STATUS_IN_PROGRESS => 'en-cours',
            self::STATUS_CONTACTED => 'contacte',
            self::STATUS_CLOSED => 'archive',
            default => 'default',
        };
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Statut de dépôt invalide.');
        }

        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function nullable(?string $value): ?string
    {
        $value = null === $value ? '' : trim($value);

        return '' === $value ? null : $value;
    }
}
