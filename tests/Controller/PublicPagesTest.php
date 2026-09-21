<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AdminDemoDataProvider;
use App\Service\AdminUserManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

    public function testAdminRequiresAuthentication(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/connexion');
    }

    public function testInvalidAdminCredentialsAreRejected(): void
    {
        $client = self::createClient();
        $this->ensureAdminUser();
        $crawler = $client->request('GET', '/admin/connexion');
        $client->submit($crawler->selectButton('Se connecter')->form([
            '_username' => 'admin@les-consultants.lu',
            '_password' => 'wrong-password',
        ]));

        self::assertResponseRedirects('/admin/connexion');
        $client->followRedirect();
        self::assertSelectorTextContains('.admin-login-error', 'Identifiant ou mot de passe incorrect.');
    }

    public function testAdminPagesAreAvailableAfterLogin(): void
    {
        $client = self::createClient();
        $this->loginAdmin($client);

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

    public function testThemePageChangesArePublished(): void
    {
        $client = self::createClient();
        $this->loginAdmin($client);

        try {
            $crawler = $client->request('GET', '/admin/contenus/home');
            $token = $crawler->filter('input[name="_token"]')->attr('value');
            $client->request('POST', '/admin/contenus/home', [
                '_token' => $token,
                'fields' => json_encode([
                    '.hero h1 span:first-child' => 'Titre publié depuis le constructeur',
                ], JSON_THROW_ON_ERROR),
                'carousels' => json_encode([
                    '.ecosystem' => ['interval' => 6200, 'mode' => 'marquee'],
                ], JSON_THROW_ON_ERROR),
            ]);

            self::assertResponseIsSuccessful();
            self::assertJson($client->getResponse()->getContent());

            $client->request('GET', '/');
            self::assertResponseIsSuccessful();
            self::assertSelectorTextSame('.hero h1 span:first-child', 'Titre publié depuis le constructeur');
            self::assertSelectorExists('.ecosystem[data-carousel-interval-value="6200"][data-carousel-mode-value="marquee"]');
        } finally {
            self::getContainer()->get(Connection::class)->executeStatement('DELETE FROM theme_page WHERE slug = ?', ['home']);
        }
    }

    public function testThemeEditorFieldsTargetExistingPageElements(): void
    {
        $client = self::createClient();
        $definitions = self::getContainer()->get(AdminDemoDataProvider::class);

        foreach ($definitions->pages() as $page) {
            $crawler = $client->request('GET', $page['path']);
            self::assertResponseIsSuccessful($page['path']);

            foreach ($page['sections'] as $section) {
                self::assertCount(1, $crawler->filter($section['selector']), $page['slug'].' : '.$section['selector']);
                foreach ($section['fields'] as $field) {
                    self::assertCount(1, $crawler->filter($field['selector']), $page['slug'].' : '.$field['selector']);
                }
                foreach ($section['carousel']['cards'] ?? [] as $card) {
                    self::assertCount(1, $crawler->filter($card['selector']), $page['slug'].' : '.$card['selector']);
                    foreach ($card['fields'] as $field) {
                        self::assertCount(1, $crawler->filter($field['selector']), $page['slug'].' : '.$field['selector']);
                    }
                }
            }
        }
    }

    public function testThemeImageCanBeUploadedAndPublished(): void
    {
        $client = self::createClient();
        $this->loginAdmin($client);
        $temporaryImage = tempnam(sys_get_temp_dir(), 'theme-image-');
        file_put_contents($temporaryImage, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));
        $publishedPath = null;

        try {
            $crawler = $client->request('GET', '/admin/contenus/about');
            $token = $crawler->filter('input[name="_token"]')->attr('value');
            $client->request('POST', '/admin/contenus/about', [
                '_token' => $token,
                'fields' => '{}',
                'carousels' => '{}',
                'imageSelectors' => ['[data-section="hero"] img'],
            ], [
                'images' => [new UploadedFile($temporaryImage, 'cabinet.png', 'image/png', null, true)],
            ]);

            self::assertResponseIsSuccessful();
            $result = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
            $publishedPath = $result['content']['[data-section="hero"] img'];

            $client->request('GET', '/a-propos');
            self::assertResponseIsSuccessful();
            self::assertSelectorExists(sprintf('[data-section="hero"] img[src="%s"]', $publishedPath));
        } finally {
            self::getContainer()->get(Connection::class)->executeStatement('DELETE FROM theme_page WHERE slug = ?', ['about']);
            if (is_string($publishedPath)) {
                $uploadedFile = self::getContainer()->getParameter('kernel.project_dir').'/public'.$publishedPath;
                if (is_file($uploadedFile)) {
                    unlink($uploadedFile);
                }
            }
            if (is_file($temporaryImage)) {
                unlink($temporaryImage);
            }
        }
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
        self::assertSelectorNotExists('.submission-flow-option');
        self::assertSelectorNotExists('form[name="consultant_application"]');
        self::assertSelectorTextSame('#submission-modal-title', 'Déposez votre mission');
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

    public function testMissionButtonsOpenTheMissionModalOnDesktopAndMobile(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');
        self::assertSelectorExists('.site-header a[href="/deposer?form=mission"]');
        self::assertSelectorExists('.mobile-nav-mission[href="/deposer?form=mission"]');

        $client->request('GET', '/deposer?form=mission');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-submission-modal-open-value="true"]');
    }

    public function testAdminCanLogout(): void
    {
        $client = self::createClient();
        $this->loginAdmin($client);
        $crawler = $client->request('GET', '/admin');
        $client->submit($crawler->filter('form[action="/admin/deconnexion"]')->form());

        self::assertResponseRedirects('/');
        $client->request('GET', '/admin');
        self::assertResponseRedirects('/admin/connexion');
    }

    public function testSuperAdminCanCreateAnAdminWithRestrictedAccess(): void
    {
        $client = self::createClient();
        $this->loginAdmin($client);
        $email = 'editor-test@les-consultants.lu';

        try {
            $crawler = $client->request('GET', '/admin/utilisateurs/nouveau');
            $client->submit($crawler->selectButton('Créer le compte')->form([
                'admin_user[displayName]' => 'Compte Test',
                'admin_user[email]' => $email,
                'admin_user[role]' => AdminUserManager::ROLE_ADMIN,
                'admin_user[active]' => true,
                'admin_user[plainPassword][first]' => 'test-password-2026',
                'admin_user[plainPassword][second]' => 'test-password-2026',
            ]));

            self::assertResponseRedirects('/admin/utilisateurs');
            $client->followRedirect();
            self::assertSelectorTextContains('.admin-users-table', $email);

            self::ensureKernelShutdown();
            $adminClient = self::createClient();
            $crawler = $adminClient->request('GET', '/admin/connexion');
            $adminClient->submit($crawler->selectButton('Se connecter')->form([
                '_username' => $email,
                '_password' => 'test-password-2026',
            ]));
            self::assertResponseRedirects('/admin');
            $adminClient->followRedirect();
            self::assertResponseIsSuccessful();

            $adminClient->request('GET', '/admin/utilisateurs');
            self::assertResponseStatusCodeSame(403);
        } finally {
            $user = self::getContainer()->get(UserRepository::class)->findOneByEmail($email);
            if ($user instanceof User) {
                $entityManager = self::getContainer()->get(EntityManagerInterface::class);
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }
    }

    private function loginAdmin(KernelBrowser $client): void
    {
        $this->ensureAdminUser();
        $crawler = $client->request('GET', '/admin/connexion');
        $client->submit($crawler->selectButton('Se connecter')->form([
            '_username' => 'admin@les-consultants.lu',
            '_password' => 'test-password',
        ]));

        self::assertResponseRedirects('/admin');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    private function ensureAdminUser(): void
    {
        $users = self::getContainer()->get(UserRepository::class);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $user = $users->findOneByEmail('admin@les-consultants.lu') ?? new User();

        $user
            ->setEmail('admin@les-consultants.lu')
            ->setDisplayName('Administrateur Test')
            ->setRoles([AdminUserManager::ROLE_SUPER_ADMIN])
            ->setActive(true)
            ->setPassword($passwordHasher->hashPassword($user, 'test-password'));
        $entityManager->persist($user);
        $entityManager->flush();
    }
}
