<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Submission;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class SubmissionMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire('%env(MISSION_NOTIFICATION_RECIPIENT)%')]
        private string $recipient,
        #[Autowire('%env(CONTACT_SENDER)%')]
        private string $sender,
    ) {
    }

    public function sendMission(Submission $submission): void
    {
        if (Submission::TYPE_MISSION !== $submission->getType()) {
            throw new \InvalidArgumentException('Seules les missions peuvent déclencher cette notification.');
        }

        $adminUrl = $this->urlGenerator->generate(
            'admin_submission_show',
            ['id' => $submission->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $email = (new TemplatedEmail())
            ->from(new Address($this->sender, 'Les Consultants'))
            ->to(new Address($this->recipient, 'Administration Les Consultants'))
            ->replyTo(new Address($submission->getEmail(), $submission->getContactName()))
            ->subject(sprintf(
                '[Nouvelle mission #%05d] %s — %s',
                $submission->getId(),
                $submission->getSubject(),
                $submission->getOrganization(),
            ))
            ->htmlTemplate('emails/submission/mission_notification.html.twig')
            ->textTemplate('emails/submission/mission_notification.txt.twig')
            ->context([
                'submission' => $submission,
                'adminUrl' => $adminUrl,
            ]);

        $this->mailer->send($email);
    }
}
