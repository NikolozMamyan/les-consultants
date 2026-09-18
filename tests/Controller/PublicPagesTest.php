<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PublicPagesTest extends WebTestCase
{
    public function testPublicPagesAreAvailable(): void
    {
        $client = self::createClient();

        foreach (['/', '/expertises', '/a-propos', '/contact', '/deposer'] as $path) {
            $client->request('GET', $path);
            self::assertResponseIsSuccessful($path);
        }
    }

    public function testFormationPageHasBeenRemoved(): void
    {
        $client = self::createClient();
        $client->request('GET', '/formations');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAdminMockupPagesAreAvailable(): void
    {
        $client = self::createClient();

        foreach ([
            '/admin',
            '/admin/contenus',
            '/admin/contenus/home',
            '/admin/blog',
            '/admin/missions-talents',
            '/admin/utilisateurs',
        ] as $path) {
            $client->request('GET', $path);
            self::assertResponseIsSuccessful($path);
        }

        $client->request('GET', '/admin/contenus');
        self::assertSelectorCount(4, '.admin-pages-table tbody tr');
        self::assertSelectorTextContains('.admin-nav', 'Missions & Talents');
    }

    public function testContactRequestCanBeSubmitted(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/contact?profil=entreprise&expertise=compliance');
        $form = $crawler->selectButton('Envoyer')->form([
            'contact_request[name]' => 'Société Exemple',
            'contact_request[email]' => 'contact@example.com',
            'contact_request[phone]' => '+352 000 000',
            'contact_request[subject]' => 'compliance',
            'contact_request[message]' => 'Nous recherchons un consultant pour une mission de conformité.',
            'contact_request[consent]' => true,
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/contact');
        $client->followRedirect();
        self::assertSelectorTextContains('.flash-message', 'Nous vous répondons sous 24h.');
    }

    public function testMissionRequestCanBeSubmitted(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/deposer');
        self::assertSelectorCount(2, '.submission-flow-option');
        self::assertSelectorNotExists('[name="mission_request[workMode]"]');

        $form = $crawler->selectButton('Envoyer la mission')->form([
            'mission_request[missionTitle]' => 'Renfort Compliance AML/KYC',
            'mission_request[expertise]' => 'Compliance & réglementation',
            'mission_request[duration]' => '3 à 6 mois',
            'mission_request[company]' => 'Exemple SA',
            'mission_request[contactName]' => 'Marie Exemple',
            'mission_request[email]' => 'marie@example.com',
            'mission_request[phone]' => '+352 000 000',
            'mission_request[description]' => 'Nous recherchons un renfort expérimenté pour accompagner notre équipe conformité.',
            'mission_request[consent]' => true,
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/deposer');
        $client->followRedirect();
        self::assertSelectorTextContains('.flash-message', 'Votre mission a bien été transmise.');
    }

    public function testInvalidMissionRequestUsesTurboValidationStatus(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/deposer');

        $client->submit($crawler->selectButton('Envoyer la mission')->form());

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('[data-submission-modal-open-value="true"]');
    }

    public function testConsultantProfileCanBeSubmittedWithPdf(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/deposer');
        $cvPath = tempnam(sys_get_temp_dir(), 'consultant-cv-');
        file_put_contents($cvPath, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");

        try {
            $form = $crawler->selectButton('Envoyer mon profil')->form([
                'consultant_application[firstName]' => 'Jean',
                'consultant_application[lastName]' => 'Consultant',
                'consultant_application[email]' => 'jean@example.com',
                'consultant_application[phone]' => '+352 111 111',
                'consultant_application[primaryDomain]' => 'Finance',
                'consultant_application[targetRoles]' => ['Compliance Officer', 'AML/KYC Officer'],
                'consultant_application[dailyRateMin]' => 550,
                'consultant_application[dailyRateMax]' => 750,
                'consultant_application[countries]' => ['Belgique'],
                'consultant_application[availability]' => 'Disponible',
                'consultant_application[consent]' => true,
            ]);
            $form['consultant_application[cv]']->upload($cvPath);

            $client->submit($form);
        } finally {
            if (is_file($cvPath)) {
                unlink($cvPath);
            }
        }

        self::assertResponseRedirects('/deposer');
        $client->followRedirect();
        self::assertSelectorTextContains('.flash-message', 'Votre profil a bien été transmis.');
    }
}
