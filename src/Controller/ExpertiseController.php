<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExpertiseController extends AbstractController
{
    #[Route('/expertises', name: 'app_expertises', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('pages/expertises.html.twig', ['page' => 'expertises']);
    }
}
