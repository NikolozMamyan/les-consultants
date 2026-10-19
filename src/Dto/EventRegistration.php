<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class EventRegistration
{
    #[Assert\NotBlank(message: 'Veuillez renseigner votre prénom.')]
    #[Assert\Length(max: 100, maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères.')]
    public string $firstName = '';

    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom.')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.')]
    public string $lastName = '';

    #[Assert\NotBlank(message: 'Veuillez renseigner votre adresse e-mail.')]
    #[Assert\Email(message: 'Veuillez renseigner une adresse e-mail valide.')]
    #[Assert\Length(max: 180, maxMessage: 'L’adresse e-mail ne doit pas dépasser {{ limit }} caractères.')]
    public string $email = '';

    #[Assert\NotBlank(message: 'Veuillez renseigner votre numéro de téléphone.')]
    #[Assert\Length(max: 35, maxMessage: 'Le numéro ne doit pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^(?=(?:\D*\d){6})\+?[0-9 ().-]{6,35}$/D', message: 'Veuillez renseigner un numéro de téléphone valide.')]
    public string $phone = '';

    #[Assert\IsTrue(message: 'Veuillez accepter l’utilisation de vos coordonnées pour votre inscription.')]
    public bool $consent = false;
}
