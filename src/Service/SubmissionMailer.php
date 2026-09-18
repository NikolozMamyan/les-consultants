<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ConsultantApplication;
use App\Dto\MissionRequest;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SubmissionMailer
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire('%env(CONTACT_RECIPIENT)%')]
        private string $recipient,
        #[Autowire('%env(CONTACT_SENDER)%')]
        private string $sender,
    ) {
    }

    public function sendMission(MissionRequest $request): void
    {
        $email = (new Email())
            ->from($this->sender)
            ->to($this->recipient)
            ->replyTo($request->email)
            ->subject(sprintf('[Nouvelle mission] %s — %s', $request->missionTitle, $request->company))
            ->text(implode("\n", [
                'NOUVEAU BESOIN ENTREPRISE',
                '',
                sprintf('Mission : %s', $request->missionTitle),
                sprintf('Expertise : %s', $request->expertise),
                sprintf('Début souhaité : %s', $request->startDate?->format('d/m/Y') ?? 'À définir'),
                sprintf('Durée : %s', $request->duration),
                '',
                sprintf('Entreprise : %s', $request->company),
                sprintf('Contact : %s', $request->contactName),
                sprintf('E-mail : %s', $request->email),
                sprintf('Téléphone : %s', $request->phone ?: 'Non renseigné'),
                '',
                'CONTEXTE ET OBJECTIFS',
                $request->description,
            ]));

        $this->mailer->send($email);
    }

    public function sendConsultant(ConsultantApplication $application): void
    {
        $email = (new Email())
            ->from($this->sender)
            ->to($this->recipient)
            ->replyTo($application->email)
            ->subject(sprintf('[Nouveau profil] %s %s — %s', $application->firstName, $application->lastName, $application->primaryDomain))
            ->text(implode("\n", [
                'NOUVEAU PROFIL CONSULTANT',
                '',
                sprintf('Prénom : %s', $application->firstName),
                sprintf('Nom : %s', $application->lastName),
                sprintf('E-mail : %s', $application->email),
                sprintf('Téléphone : %s', $application->phone),
                '',
                sprintf('Domaine principal : %s', $application->primaryDomain),
                sprintf('Postes visés : %s', implode(', ', $application->targetRoles)),
                sprintf(
                    'TJM souhaité : %d € minimum%s',
                    $application->dailyRateMin,
                    null !== $application->dailyRateMax ? sprintf(' — %d € maximum', $application->dailyRateMax) : '',
                ),
                sprintf('Pays : %s', implode(', ', $application->countries)),
                sprintf('Disponibilité : %s', $application->availability),
            ]));

        if (null !== $application->cv) {
            $email->attachFromPath(
                $application->cv->getPathname(),
                $application->cv->getClientOriginalName(),
                $application->cv->getMimeType() ?: 'application/pdf',
            );
        }

        $this->mailer->send($email);
    }
}
