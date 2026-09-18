<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\AdminDemoDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class AdminController extends AbstractController
{
    public function __construct(private readonly AdminDemoDataProvider $data)
    {
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
            'pages' => $this->data->pages(),
        ]);
    }

    #[Route('/contenus/{slug}', name: 'content_edit', methods: ['GET'], requirements: ['slug' => 'home|services|about|contact'])]
    public function editContent(string $slug): Response
    {
        $page = $this->data->page($slug);

        if (null === $page) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/content/edit.html.twig', [
            'adminSection' => 'content',
            'pageData' => $page,
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

    #[Route('/utilisateurs', name: 'users', methods: ['GET'])]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig', [
            'adminSection' => 'users',
            'users' => $this->data->users(),
        ]);
    }
}
