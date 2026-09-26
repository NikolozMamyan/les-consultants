<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_notice', methods: ['GET'])]
    public function legalNotice(): Response
    {
        return $this->render('pages/legal_notice.html.twig', ['page' => 'legal']);
    }

    #[Route('/politique-de-confidentialite', name: 'app_privacy_policy', methods: ['GET'])]
    public function privacyPolicy(): Response
    {
        return $this->render('pages/privacy_policy.html.twig', ['page' => 'legal']);
    }
}
