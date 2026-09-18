<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ContactRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public ?string $name = null;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\Length(max: 35)]
    public ?string $phone = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['compliance', 'finance', 'risques', 'juridique', 'digital', 'formation', 'autre'])]
    public ?string $subject = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 3000)]
    public ?string $message = null;

    #[Assert\Choice(choices: ['entreprise', 'consultant'])]
    public string $profile = 'entreprise';

    #[Assert\IsTrue(message: 'Vous devez accepter la politique de confidentialité.')]
    public bool $consent = false;
}
