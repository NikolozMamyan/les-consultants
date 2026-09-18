<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class ConsultantApplication
{
    public const FINANCE_ROLES = [
        'Internal Auditor',
        'Treasury Analyst',
        'NAV Analyst',
        'Transfer Agent',
        'Custody Specialist',
        'Collateral Management Analyst',
        'Regulatory Officer',
        'AML/KYC Officer',
        'Relationship Manager',
        'Credit Analyst',
        'Risk Analyst',
        'Compliance Officer',
        'Investment Analyst',
        'Portfolio Manager',
        'Back Office Officer',
        'Middle Office Officer',
        'Operations Officer',
        'Fund Accountant',
        'Wealth Manager',
        'Private Banker',
        'Risk Officer',
        'Actuaire',
        'Insurance Product Manager',
        'Claims Handler',
        'Underwriter',
        'M&A Analyst',
        'Management Controller',
        'External Auditor',
        'Tax Advisor',
        'Financial Controller',
        'Corporate Finance Analyst',
        'Risk Manager',
        'Fund Legal Officer',
        'Valuation Officer',
        'Fund Compliance Officer',
        'Portfolio Analyst',
    ];

    public const IT_ROLES = [
        'Software Developer',
        'Front-End Developer',
        'Back-End Developer',
        'DevOps Engineer',
        'Full Stack Developer',
        'Data Engineer',
        'Data Scientist',
        'Data Analyst',
        'AI Engineer',
        'Machine Learning Engineer',
        'IT System Administrator',
        'Cloud Engineer',
        'Network & Security Engineer',
        'IT Support',
        'IT Project Manager',
        'IT Business Analyst',
        'IT Security Officer',
        'Cybersecurity Analyst',
        'CISO (Chief Information Security Officer)',
        'DPO (Data Protection Officer)',
        'IT Risk Officer',
        'IT Compliance',
        'IT Governance Analyst',
        'SOC Analyst',
        'BI Analyst',
        'ERP Specialist',
        'CRM Specialist',
        'RPA Developer',
    ];

    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 35)]
    public ?string $phone = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['Finance', 'IT'])]
    public ?string $primaryDomain = null;

    /** @var list<string> */
    #[Assert\Count(min: 1, minMessage: 'Sélectionnez au moins un poste visé.')]
    public array $targetRoles = [];

    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 5000)]
    public ?int $dailyRateMin = null;

    #[Assert\Range(min: 0, max: 5000)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'dailyRateMin', message: 'Le TJM maximum doit être supérieur ou égal au TJM minimum.')]
    public ?int $dailyRateMax = null;

    /** @var list<string> */
    #[Assert\Count(min: 1, minMessage: 'Sélectionnez au moins un pays.')]
    public array $countries = [];

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['Disponible', 'Non disponible'])]
    public ?string $availability = null;

    #[Assert\NotNull(message: 'Ajoutez votre CV au format PDF.')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['application/pdf'],
        mimeTypesMessage: 'Le CV doit être au format PDF.',
    )]
    public ?UploadedFile $cv = null;

    #[Assert\IsTrue(message: 'Vous devez accepter la politique de confidentialité.')]
    public bool $consent = false;
}
