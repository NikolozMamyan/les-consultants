<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CatalogPageRepository;
use App\Repository\CatalogRepository;
use App\Service\CatalogContentsBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalog', methods: ['GET'])]
    public function __invoke(CatalogRepository $catalogs, CatalogPageRepository $pages, CatalogContentsBuilder $contentsBuilder): Response
    {
        $catalog = $catalogs->findCurrent();
        if (null === $catalog || !$catalog->isEnabled()) {
            return $this->redirectToRoute('app_expertises');
        }

        $visiblePages = $pages->findForCatalog($catalog, true);
        if ([] === $visiblePages) {
            return $this->redirectToRoute('app_expertises');
        }

        $contentsEntries = $contentsBuilder->build($catalog, $visiblePages);

        return $this->render('catalog/index.html.twig', [
            'catalog' => $catalog,
            'catalogPages' => $visiblePages,
            'contentsEntries' => $contentsEntries,
            'contentsPages' => $contentsBuilder->paginate($contentsEntries),
            'isPreview' => false,
        ]);
    }
}
