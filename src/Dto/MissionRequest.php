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
        'Compliance & regulation',
        'Internal control & governance',
        'Legal & regulatory services',
        'Fund Administration',
        'Digital transformation (IT)',
        'Training',
        'Other',
    ])]
    public ?string $expertise = null;

    #[Assert\GreaterThanOrEqual('today', message: 'The start date must be today or later.')]
    public ?\DateTimeImmutable $startDate = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['Less than 3 months', '3 to 6 months', 'More than 6 months', 'To be confirmed'])]
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

    #[Assert\IsTrue(message: 'You must accept the privacy policy.')]
    public bool $consent = false;
}
