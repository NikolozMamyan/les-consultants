<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EventRegistrationSettings
{
    #[ORM\Id]
    #[ORM\Column(length: 80)]
    private string $eventSlug;

    #[ORM\Column]
    private bool $registrationOpen = false;

    public function __construct(string $eventSlug)
    {
        $this->eventSlug = $eventSlug;
    }

    public function isRegistrationOpen(): bool
    {
        return $this->registrationOpen;
    }

    public function setRegistrationOpen(bool $registrationOpen): void
    {
        $this->registrationOpen = $registrationOpen;
    }
}
