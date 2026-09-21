<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\AdminDemoDataProvider;
use App\Service\ThemePageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class AdminController extends AbstractController
{
    public function __construct(
        private readonly AdminDemoDataProvider $data,
        private readonly ThemePageManager $themePages,
    ) {
    }

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', $this->data->dashboard() + ['adminSection' => 'dashboard']);
    }

    #[Route('/contenus', name: 'content', methods: ['GET'])]
    public function content(): Response
    {
        return $this->render('admin/content/index.html.twig', [
            'adminSection' => 'content',
            'pages' => $this->themePages->pages(),
        ]);
    }

    #[Route('/contenus/{slug}', name: 'content_edit', methods: ['GET'], requirements: ['slug' => 'home|services|about|contact'])]
    public function editContent(string $slug): Response
    {
        $page = $this->themePages->page($slug);

        if (null === $page) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/content/edit.html.twig', [
            'adminSection' => 'content',
            'pageData' => $page,
        ]);
    }

    #[Route('/contenus/{slug}', name: 'content_update', methods: ['POST'], requirements: ['slug' => 'home|services|about|contact'])]
    public function updateContent(string $slug, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('theme_page_'.$slug, $request->request->getString('_token'))) {
            return $this->json(['message' => 'Le formulaire a expiré. Rechargez la page puis réessayez.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $fields = json_decode($request->request->getString('fields', '{}'), true, flags: JSON_THROW_ON_ERROR);
            $carousels = json_decode($request->request->getString('carousels', '{}'), true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($fields) || !is_array($carousels)) {
                throw new \InvalidArgumentException('Les données envoyées sont invalides.');
            }

            $selectors = array_values($request->request->all('imageSelectors'));
            $files = array_values($request->files->all('images'));
            if (count($selectors) !== count($files)) {
                throw new \InvalidArgumentException('Les images envoyées sont invalides.');
            }

            $images = [];
            foreach ($files as $index => $file) {
                if (!is_string($selectors[$index]) || !$file instanceof UploadedFile) {
                    throw new \InvalidArgumentException('Une image envoyée est invalide.');
                }
                $images[] = ['selector' => $selectors[$index], 'file' => $file];
            }

            $page = $this->themePages->save($slug, $fields, $carousels, $images);
        } catch (\JsonException|\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json([
            'message' => 'Les modifications ont été publiées.',
            'content' => $page->getContent(),
            'updatedAt' => $page->getUpdatedAt()->format(DATE_ATOM),
        ]);
    }

    #[Route('/blog', name: 'blog', methods: ['GET'])]
    public function blog(): Response
    {
        return $this->render('admin/blog.html.twig', [
            'adminSection' => 'blog',
            'posts' => $this->data->posts(),
        ]);
    }

    #[Route('/missions-talents', name: 'submissions', methods: ['GET'])]
    public function submissions(): Response
    {
        return $this->render('admin/submissions.html.twig', [
            'adminSection' => 'submissions',
            'submissions' => $this->data->submissions(),
        ]);
    }

}
