<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CatalogPage;
use App\Repository\CatalogPageRepository;
use App\Service\CatalogContentsBuilder;
use App\Service\CatalogManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/catalogue', name: 'admin_catalog')]
final class CatalogController extends AbstractController
{
    public function __construct(
        private readonly CatalogManager $manager,
        private readonly CatalogPageRepository $pages,
        private readonly CatalogContentsBuilder $contentsBuilder,
    ) {
    }

    #[Route('', name: '', methods: ['GET'])]
    public function index(): Response
    {
        $catalog = $this->manager->current();
        $catalogPages = $this->pages->findForCatalog($catalog);
        $contentsEntries = $this->contentsBuilder->build($catalog, $catalogPages);

        return $this->render('admin/catalog/index.html.twig', [
            'adminSection' => 'catalog',
            'catalog' => $catalog,
            'catalogPages' => $catalogPages,
            'contentsEntries' => $contentsEntries,
            'contentsPages' => $this->contentsBuilder->paginate($contentsEntries),
        ]);
    }

    #[Route('/apercu', name: '_preview', methods: ['GET'])]
    public function preview(): Response
    {
        $catalog = $this->manager->current();
        $visiblePages = $this->pages->findForCatalog($catalog, true);
        if ([] === $visiblePages) {
            $this->addFlash('error', 'Activez au moins une page avant d’ouvrir l’aperçu.');

            return $this->redirectToRoute('admin_catalog');
        }

        $contentsEntries = $this->contentsBuilder->build($catalog, $visiblePages);

        return $this->render('catalog/index.html.twig', [
            'catalog' => $catalog,
            'catalogPages' => $visiblePages,
            'contentsEntries' => $contentsEntries,
            'contentsPages' => $this->contentsBuilder->paginate($contentsEntries),
            'isPreview' => true,
        ]);
    }

    #[Route('/parametres', name: '_settings', methods: ['POST'])]
    public function settings(Request $request): RedirectResponse
    {
        $this->validateToken('catalog_settings', $request);
        try {
            $this->manager->saveSettings(
                $this->manager->current(),
                $request->request->getString('title'),
                $request->request->getBoolean('enabled'),
            );
            $this->addFlash('success', 'Les paramètres du catalogue ont été enregistrés.');
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_catalog');
    }

    #[Route('/sommaire', name: '_contents', methods: ['POST'])]
    public function contents(Request $request): RedirectResponse
    {
        $this->validateToken('catalog_contents', $request);
        try {
            $this->manager->saveContents(
                $this->manager->current(),
                $request->request->getString('contentsTitle'),
                $request->request->all('contentsLabels'),
                $request->request->getBoolean('resetLabels'),
                $request->request->getBoolean('resetOrder'),
            );
            $this->addFlash('success', match (true) {
                $request->request->getBoolean('resetLabels') => 'Les noms automatiques du sommaire ont été rétablis.',
                $request->request->getBoolean('resetOrder') => 'L’ordre automatique du sommaire a été rétabli.',
                default => 'Le sommaire a été mis à jour.',
            });
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_catalog', ['_fragment' => 'sommaire']);
    }

    #[Route('/sommaire/reordonner', name: '_contents_reorder', methods: ['POST'])]
    public function reorderContents(Request $request): JsonResponse
    {
        $this->validateToken('catalog_contents_reorder', $request);
        try {
            $this->manager->reorderContents($this->manager->current(), $request->request->all('contentsKeys'));

            return $this->json(['ok' => true]);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['ok' => false, 'message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/ajouter-images', name: '_add_images', methods: ['POST'])]
    public function addImages(Request $request): RedirectResponse
    {
        $this->validateToken('catalog_add_images', $request);
        try {
            $count = $this->manager->addImages($this->manager->current(), $request->files->all('images'));
            $this->addFlash('success', sprintf('%d page%s ajoutée%s au catalogue.', $count, $count > 1 ? 's' : '', $count > 1 ? 's' : ''));
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_catalog');
    }

    #[Route('/importer-pdf', name: '_import_pdf', methods: ['POST'])]
    public function importPdf(Request $request): RedirectResponse
    {
        $this->validateToken('catalog_import_pdf', $request);
        $files = $request->files->all('pdfs');
        $expectedFileCount = $request->request->getInt('pdfFileCount');
        if ($expectedFileCount > count($files)) {
            $this->addFlash('error', sprintf(
                'Le serveur n’a reçu que %d PDF sur %d. Augmentez la valeur PHP « max_file_uploads » puis recommencez.',
                count($files),
                $expectedFileCount,
            ));

            return $this->redirectToRoute('admin_catalog');
        }

        try {
            try {
                $pageCounts = json_decode($request->request->getString('pdfPageCounts'), true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $pageCounts = [];
            }
            if (!is_array($pageCounts)) {
                $pageCounts = [];
            }

            $result = $this->manager->addPdfs(
                $this->manager->current(),
                $files,
                $pageCounts,
                $request->request->getString('pdfTitle'),
            );
            $this->addFlash('success', sprintf(
                '%d PDF%s importé%s en %d page%s.',
                $result['documents'],
                $result['documents'] > 1 ? 's' : '',
                $result['documents'] > 1 ? 's' : '',
                $result['pages'],
                $result['pages'] > 1 ? 's' : '',
            ));
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_catalog');
    }

    #[Route('/reordonner', name: '_reorder', methods: ['POST'])]
    public function reorder(Request $request): JsonResponse
    {
        $this->validateToken('catalog_reorder', $request);
        try {
            $this->manager->reorder($this->manager->current(), $request->request->all('pageIds'));

            return $this->json(['ok' => true]);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['ok' => false, 'message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/page/{id}', name: '_page_update', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function updatePage(int $id, Request $request): RedirectResponse
    {
        $page = $this->findPage($id);
        $this->validateToken('catalog_page_'.$id, $request);
        $replacement = $request->files->get('media');
        try {
            $this->manager->updatePage(
                $page,
                $request->request->getString('title'),
                $request->request->getString('linkUrl'),
                $request->request->getBoolean('openInNewTab'),
                $request->request->getBoolean('enabled'),
                $replacement instanceof UploadedFile ? $replacement : null,
            );
            $this->addFlash('success', sprintf('La page %d a été mise à jour.', $page->getPosition()));
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_catalog', ['_fragment' => 'page-'.$id]);
    }

    #[Route('/page/{id}/deplacer/{direction}', name: '_page_move', methods: ['POST'], requirements: ['id' => '\\d+', 'direction' => 'up|down'])]
    public function movePage(int $id, string $direction, Request $request): RedirectResponse
    {
        $page = $this->findPage($id);
        $this->validateToken('catalog_move_'.$id, $request);
        $this->manager->move($page, $direction);

        return $this->redirectToRoute('admin_catalog', ['_fragment' => 'pages']);
    }

    #[Route('/page/{id}/supprimer', name: '_page_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function deletePage(int $id, Request $request): RedirectResponse
    {
        $page = $this->findPage($id);
        $this->validateToken('catalog_delete_'.$id, $request);
        $this->manager->delete($page);
        $this->addFlash('success', 'La page a été supprimée du catalogue.');

        return $this->redirectToRoute('admin_catalog', ['_fragment' => 'pages']);
    }

    private function findPage(int $id): CatalogPage
    {
        $page = $this->pages->find($id);
        if (!$page instanceof CatalogPage || $page->getCatalog()->getId() !== $this->manager->current()->getId()) {
            throw $this->createNotFoundException('Page de catalogue introuvable.');
        }

        return $page;
    }

    private function validateToken(string $id, Request $request): void
    {
        if (!$this->isCsrfTokenValid($id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Le formulaire a expiré.');
        }
    }
}
