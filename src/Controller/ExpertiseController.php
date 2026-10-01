<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CatalogRepository;
use App\Repository\CatalogPageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExpertiseController extends AbstractController
{
    #[Route('/expertises', name: 'app_expertises', methods: ['GET'])]
    public function __invoke(CatalogRepository $catalogs, CatalogPageRepository $pages): Response
    {
        $catalog = $catalogs->findCurrent();

        return $this->render('pages/expertises.html.twig', [
            'page' => 'expertises',
            'catalogAvailable' => null !== $catalog && $catalog->isEnabled() && 0 < $pages->count(['catalog' => $catalog, 'enabled' => true]),
        ]);
    }
}
