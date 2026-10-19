<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventRegistrationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRegistrationRepository::class)]
#[ORM\Table(name: 'event_registration')]
#[ORM\UniqueConstraint(name: 'uniq_event_registration_email', columns: ['event_slug', 'email'])]
#[ORM\Index(name: 'idx_event_registration_created_at', columns: ['event_slug', 'created_at'])]
class EventRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private string $eventSlug;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 35)]
    private string $phone;

    #[ORM\Column]
    private bool $consent;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $eventSlug, string $firstName, string $lastName, string $email, string $phone, bool $consent)
    {
        $this->eventSlug = $eventSlug;
        $this->firstName = trim($firstName);
        $this->lastName = trim($lastName);
        $this->email = mb_strtolower(trim($email));
        $this->phone = trim($phone);
        $this->consent = $consent;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventSlug(): string
    {
        return $this->eventSlug;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function hasConsent(): bool
    {
        return $this->consent;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
