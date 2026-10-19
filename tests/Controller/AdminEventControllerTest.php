<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\EventRegistration;
use App\Entity\User;
use App\Service\EventRegistrationManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminEventControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ?Connection $connection = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->client->disableReboot();
        $this->connection = self::getContainer()->get(Connection::class);
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->connection?->isTransactionActive()) {
            $this->connection->rollBack();
        }
        parent::tearDown();
    }

    public function testTheListAndExportRequireAdminAuthentication(): void
    {
        foreach (['/admin/evenements', '/admin/evenements/export.csv'] as $path) {
            $this->client->request('GET', $path);
            self::assertResponseRedirects('/admin/connexion');
        }
    }

    public function testAnAdminCanSeeTheEventAndItsRegistrations(): void
    {
        $this->loginAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist(new EventRegistration(EventRegistrationManager::EVENT_SLUG, 'Élodie', 'Martin', 'event-admin-test@example.com', '+352 621 123 456', true));
        $entityManager->persist(new EventRegistration('another-event', 'Autre', 'Invité', 'other-event@example.com', '+352 621 123 457', true));
        $entityManager->flush();

        $this->client->request('GET', '/admin/evenements');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.admin-nav a[aria-current="page"]', 'Événements');
        self::assertSelectorTextContains('.admin-event-table', 'Élodie');
        self::assertSelectorTextContains('.admin-event-table', 'event-admin-test@example.com');
        self::assertSelectorTextNotContains('.admin-event-table', 'other-event@example.com');
        self::assertSelectorExists('a[href="/admin/evenements/export.csv"][data-turbo="false"]');
        self::assertStringContainsString('no-store', $this->client->getResponse()->headers->get('Cache-Control'));
    }

    public function testAnAdminCanSeeTheEmptyState(): void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/admin/evenements');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.admin-empty-state', 'Aucune inscription pour le moment');
    }

    public function testTheExcelExportUsesSemicolonsAndProtectsSpreadsheetCells(): void
    {
        $this->loginAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist(new EventRegistration(EventRegistrationManager::EVENT_SLUG, '=2+2', 'Martin;Dupont', 'event-export-test@example.com', '+352 621 123 456', true));
        $entityManager->persist(new EventRegistration('another-event', 'Autre', 'Invité', 'other-event@example.com', '+352 621 123 457', true));
        $entityManager->flush();

        $this->client->request('GET', '/admin/evenements/export.csv');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/csv; charset=UTF-8');
        self::assertStringContainsString('attachment;', $this->client->getResponse()->headers->get('Content-Disposition'));
        self::assertStringContainsString('.csv', $this->client->getResponse()->headers->get('Content-Disposition'));
        $csv = $this->client->getInternalResponse()->getContent();
        self::assertStringStartsWith("\xEF\xBB\xBFsep=;\r\n", $csv);
        self::assertStringNotContainsString('other-event@example.com', $csv);

        $stream = fopen('php://memory', 'w+');
        fwrite($stream, substr($csv, strlen("\xEF\xBB\xBFsep=;\r\n")));
        rewind($stream);
        self::assertSame(['Prénom', 'Nom', 'E-mail', 'Téléphone', 'Inscrit le', 'Consentement'], fgetcsv($stream, separator: ';', escape: ''));
        $row = fgetcsv($stream, separator: ';', escape: '');
        self::assertSame("'=2+2", $row[0]);
        self::assertSame('Martin;Dupont', $row[1]);
        self::assertSame('event-export-test@example.com', $row[2]);
        self::assertSame("'+352 621 123 456", $row[3]);
        self::assertSame('Oui', $row[5]);
        self::assertFalse(fgetcsv($stream, separator: ';', escape: ''));
        fclose($stream);
    }

    private function loginAdmin(): void
    {
        $user = (new User())
            ->setEmail('event-admin-user-test@example.com')
            ->setDisplayName('Administrateur Événements')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('unused-password');
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->client->loginUser($user);
    }
}
