<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\EventRegistration as RegistrationData;
use App\Entity\EventRegistration;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class EventRegistrationManager
{
    public const EVENT_SLUG = 'after-champagne-work-2026';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRegistrationRepository $registrations,
    ) {
    }

    public function register(RegistrationData $data): void
    {
        $email = mb_strtolower(trim($data->email));
        if (null !== $this->registrations->findOneBy(['eventSlug' => self::EVENT_SLUG, 'email' => $email])) {
            return;
        }

        $registration = new EventRegistration(self::EVENT_SLUG, $data->firstName, $data->lastName, $email, $data->phone, $data->consent);
        $this->entityManager->persist($registration);
        $this->entityManager->flush();
    }

    /** @param resource $output */
    public function exportCsv($output): void
    {
        fwrite($output, "\xEF\xBB\xBFsep=;\r\n");
        fputcsv($output, ['Prénom', 'Nom', 'E-mail', 'Téléphone', 'Inscrit le', 'Consentement'], ';', '"', '');

        foreach ($this->registrations->findForEvent(self::EVENT_SLUG) as $registration) {
            $values = [
                $registration->getFirstName(),
                $registration->getLastName(),
                $registration->getEmail(),
                $registration->getPhone(),
                $registration->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y H:i'),
                $registration->hasConsent() ? 'Oui' : 'Non',
            ];

            fputcsv($output, array_map(
                static fn (string $value): string => preg_match('/^[=+\-@\t\r\n]/', $value) ? "'".$value : $value,
                $values,
            ), ';', '"', '');
        }
    }
}
