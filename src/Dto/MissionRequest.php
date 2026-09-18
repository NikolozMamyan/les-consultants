<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class MissionRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public ?string $missionTitle = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: [
        'Compliance & réglementation',
        'Contrôle interne & gouvernance',
        'Services juridiques & réglementaires',
        'Fund Administration',
        'Transformation digitale (IT)',
        'Formation',
        'Autre',
    ])]
    public ?string $expertise = null;

    #[Assert\GreaterThanOrEqual('today', message: 'La date de début doit être aujourd’hui ou ultérieure.')]
    public ?\DateTimeImmutable $startDate = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['Moins de 3 mois', '3 à 6 mois', 'Plus de 6 mois', 'À définir'])]
    public ?string $duration = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public ?string $company = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public ?string $contactName = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(max: 35)]
    public ?string $phone = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 20, max: 4000)]
    public ?string $description = null;

    #[Assert\IsTrue(message: 'Vous devez accepter la politique de confidentialité.')]
    public bool $consent = false;
}
