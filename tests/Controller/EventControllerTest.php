<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\EventRegistrationRepository;
use App\Service\EventRegistrationManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DatabaseException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    private const PATH = '/after-champagne-work';
    private const EMAIL = 'elodie-event-test@example.com';

    private KernelBrowser $client;
    private ?Connection $connection = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->client->disableReboot();
        $this->client->setServerParameter('HTTP_ORIGIN', 'http://localhost');
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

    public function testTheEventPageIsPublicAndNotIndexed(): void
    {
        $this->client->request('GET', self::PATH);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
        self::assertSelectorExists('html[lang="fr"]');
        self::assertSelectorExists('meta[name="robots"][content="noindex,nofollow"]');
        self::assertSelectorTextContains('time', 'Jeudi 19 novembre 2026');
        self::assertSelectorTextContains('.event-details', 'Hôtel Royal');
        self::assertSelectorCount(4, '.field input[required]');
        self::assertSelectorNotExists('.main-nav');
        self::assertSelectorNotExists('.event-thanks');

        foreach (['/', '/sitemap.xml'] as $path) {
            $this->client->request('GET', $path);
            self::assertResponseIsSuccessful();
            self::assertStringNotContainsString(self::PATH, $this->client->getResponse()->getContent());
        }
    }

    public function testRegistrationIsSavedBeforeShowingThankYou(): void
    {
        $crawler = $this->client->request('GET', self::PATH);
        $this->client->submit($crawler->selectButton('Je m’inscris')->form($this->validFields()));

        self::assertResponseRedirects(self::PATH, 303);
        $registrations = self::getContainer()->get(EventRegistrationRepository::class);
        $registration = $registrations->findOneBy(['eventSlug' => EventRegistrationManager::EVENT_SLUG, 'email' => self::EMAIL]);
        self::assertNotNull($registration);
        self::assertSame('Élodie', $registration->getFirstName());
        self::assertSame('Martin', $registration->getLastName());
        self::assertSame('+352 621 123 456', $registration->getPhone());
        self::assertTrue($registration->hasConsent());

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.event-thanks', 'Merci pour');
        self::assertSelectorNotExists('form');
    }

    public function testTheSameEmailCannotRegisterTwice(): void
    {
        foreach ([self::EMAIL, strtoupper(self::EMAIL)] as $email) {
            $crawler = $this->client->request('GET', self::PATH);
            $fields = $this->validFields();
            $fields['event_registration[email]'] = $email;
            $this->client->submit($crawler->selectButton('Je m’inscris')->form($fields));
            self::assertResponseRedirects(self::PATH, 303);
            $this->client->followRedirect();
            self::assertSelectorExists('.event-thanks');
        }

        self::assertSame(1, $this->registrationCount());
    }

    #[DataProvider('invalidFields')]
    public function testInvalidRegistrationIsNotSaved(string $field, mixed $value): void
    {
        $crawler = $this->client->request('GET', self::PATH);
        $fields = $this->validFields();
        $fields['event_registration['.$field.']'] = $value;
        $this->client->submit($crawler->selectButton('Je m’inscris')->form($fields));

        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->registrationCount());
        self::assertSelectorNotExists('.event-thanks');
    }

    public static function invalidFields(): iterable
    {
        yield 'missing first name' => ['firstName', '   '];
        yield 'invalid email' => ['email', 'invalid-address'];
        yield 'invalid phone' => ['phone', '------'];
        yield 'missing consent' => ['consent', false];
    }

    public function testMissingCsrfTokenPreventsRegistration(): void
    {
        $this->client->request('POST', self::PATH, [
            'event_registration' => [
                'firstName' => 'Élodie',
                'lastName' => 'Martin',
                'email' => self::EMAIL,
                'phone' => '+352 621 123 456',
                'consent' => '1',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.event-form-errors', 'Le formulaire a expiré.');
        self::assertSame(0, $this->registrationCount());
    }

    public function testDatabaseFailureKeepsFormDataWithoutShowingThankYou(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('flush')->willThrowException(new class('Database unavailable') extends \RuntimeException implements DatabaseException {});
        self::getContainer()->set(EventRegistrationManager::class, new EventRegistrationManager(
            $entityManager,
            self::getContainer()->get(EventRegistrationRepository::class),
        ));

        $crawler = $this->client->request('GET', self::PATH);
        $this->client->submit($crawler->selectButton('Je m’inscris')->form($this->validFields()));

        self::assertResponseStatusCodeSame(503);
        self::assertSelectorTextContains('.event-form-errors', 'Votre inscription n’a pas pu être confirmée.');
        self::assertInputValueSame('event_registration[firstName]', 'Élodie');
        self::assertInputValueSame('event_registration[email]', self::EMAIL);
        self::assertSelectorNotExists('.event-thanks');
        self::assertSame(0, $this->registrationCount());
    }

    private function registrationCount(): int
    {
        return self::getContainer()->get(EventRegistrationRepository::class)->count([
            'eventSlug' => EventRegistrationManager::EVENT_SLUG,
            'email' => self::EMAIL,
        ]);
    }

    private function validFields(): array
    {
        return [
            'event_registration[firstName]' => 'Élodie',
            'event_registration[lastName]' => 'Martin',
            'event_registration[email]' => self::EMAIL,
            'event_registration[phone]' => '+352 621 123 456',
            'event_registration[consent]' => true,
        ];
    }
}
