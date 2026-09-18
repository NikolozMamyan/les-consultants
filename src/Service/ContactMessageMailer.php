<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ContactRequest;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class ContactMessageMailer
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire('%env(CONTACT_RECIPIENT)%')]
        private string $recipient,
        #[Autowire('%env(CONTACT_SENDER)%')]
        private string $sender,
    ) {
    }

    public function send(ContactRequest $request): void
    {
        $email = (new Email())
            ->from($this->sender)
            ->to($this->recipient)
            ->replyTo($request->email)
            ->subject(sprintf('[Site Les Consultants] %s — %s', $request->subject, $request->name))
            ->text(implode("\n", [
                sprintf('Profil : %s', $request->profile),
                sprintf('Nom : %s', $request->name),
                sprintf('E-mail : %s', $request->email),
                sprintf('Téléphone : %s', $request->phone ?: 'Non renseigné'),
                sprintf('Sujet : %s', $request->subject),
                '',
                $request->message,
            ]));

        $this->mailer->send($email);
    }
}
