<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\OnlineVisitorAnalytics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/audience-en-direct', name: 'admin_online')]
final class OnlineVisitorController extends AbstractController
{
    public function __construct(private readonly OnlineVisitorAnalytics $analytics)
    {
    }

    #[Route('', name: '', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/online/index.html.twig', [
            'adminSection' => 'online',
            'snapshot' => $this->analytics->snapshot(),
        ]);
    }

    #[Route('/donnees', name: '_data', methods: ['GET'])]
    public function data(): JsonResponse
    {
        $response = $this->json($this->analytics->snapshot());
        $response->setPrivate();
        $response->setMaxAge(0);

        return $response;
    }
}
