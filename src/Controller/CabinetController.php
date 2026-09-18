<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CabinetController extends AbstractController
{
    #[Route('/a-propos', name: 'app_cabinet', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('pages/cabinet.html.twig', ['page' => 'cabinet']);
    }
}
