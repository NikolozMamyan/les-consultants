<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Catalog;
use App\Entity\CatalogPage;
use App\Repository\CatalogPageRepository;
use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class CatalogManager
{
    private const MAX_IMAGE_SIZE = 15 * 1024 * 1024;
    private const MAX_PDF_SIZE = 90 * 1024 * 1024;
    private const MAX_PDF_FILES = 50;
    private const MAX_PDF_PAGES = 200;
    private const UPLOAD_PREFIX = '/uploads/catalogue/';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CatalogRepository $catalogs,
        private CatalogPageRepository $pages,
        private CatalogContentsBuilder $contentsBuilder,
        #[Autowire('%kernel.project_dir%/public')]
        private string $publicDirectory,
    ) {
    }

    public function current(): Catalog
    {
        return $this->catalogs->findCurrent() ?? throw new \LogicException('Le catalogue n’est pas initialisé. Exécutez les migrations Doctrine.');
    }

    public function saveSettings(Catalog $catalog, string $title, bool $enabled): void
    {
        $title = trim($title);
        if ('' === $title || mb_strlen($title) > 180) {
            throw new \InvalidArgumentException('Le titre doit contenir entre 1 et 180 caractères.');
        }

        $catalog->setTitle($title)->setEnabled($enabled);
        $catalog->touch();
        $this->entityManager->flush();
    }

    /** @param array<string, mixed> $submittedLabels */
    public function saveContents(Catalog $catalog, string $title, array $submittedLabels, bool $resetLabels, bool $resetOrder): void
    {
        $title = trim($title);
        if ('' === $title || mb_strlen($title) > 100) {
            throw new \InvalidArgumentException('Le titre du sommaire doit contenir entre 1 et 100 caractères.');
        }

        $labels = [];
        if (!$resetLabels) {
            foreach ($this->contentsBuilder->build($catalog, $this->pages->findForCatalog($catalog)) as $entry) {
                $label = $submittedLabels[$entry['key']] ?? '';
                if (!is_string($label)) {
                    continue;
                }

                $label = trim($label);
                if (mb_strlen($label) > 180) {
                    throw new \InvalidArgumentException('Chaque intitulé du sommaire est limité à 180 caractères.');
                }
                if ('' !== $label && $label !== $entry['generatedLabel']) {
                    $labels[$entry['key']] = $label;
                }
            }
        }

        $catalog->setContentsTitle($title)->setContentsLabels($labels);
        if ($resetOrder) {
            $catalog->setContentsOrder([]);
        }
        $catalog->touch();
        $this->entityManager->flush();
    }

    /** @param array<int, mixed> $submittedKeys */
    public function reorderContents(Catalog $catalog, array $submittedKeys): void
    {
        $submittedKeys = array_values(array_unique(array_filter(
            $submittedKeys,
            static fn (mixed $key): bool => is_string($key) && 1 === preg_match('/^[a-f0-9]{64}$/', $key),
        )));
        $currentKeys = array_column($this->contentsBuilder->build($catalog, $this->pages->findForCatalog($catalog)), 'key');
        $sortedSubmittedKeys = $submittedKeys;
        $sortedCurrentKeys = $currentKeys;
        sort($sortedSubmittedKeys);
        sort($sortedCurrentKeys);
        if ($sortedSubmittedKeys !== $sortedCurrentKeys) {
            throw new \InvalidArgumentException('L’ordre du sommaire est incomplet ou invalide. Rechargez la page puis réessayez.');
        }

        $catalog->setContentsOrder($submittedKeys);
        $catalog->touch();
        $this->entityManager->flush();
    }

    /** @param list<UploadedFile> $files */
    public function addImages(Catalog $catalog, array $files): int
    {
        $files = array_values(array_filter($files, static fn (mixed $file): bool => $file instanceof UploadedFile));
        if ([] === $files) {
            throw new \InvalidArgumentException('Sélectionnez au moins une image.');
        }
        if (count($files) > 50) {
            throw new \InvalidArgumentException('Vous pouvez ajouter au maximum 50 images à la fois.');
        }

        $position = $this->pages->nextPosition($catalog);
        foreach ($files as $file) {
            $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $page = (new CatalogPage($catalog, $position++))
                ->setTitle(mb_substr('' !== trim($title) ? $title : 'Nouvelle page', 0, 180))
                ->setImagePath($this->storeImage($file));
            $this->entityManager->persist($page);
        }

        $catalog->touch();
        $this->entityManager->flush();

        return count($files);
    }

    /**
     * @param list<UploadedFile> $files
     * @param array<int, mixed>  $requestedPageCounts
     *
     * @return array{documents: int, pages: int}
     */
    public function addPdfs(Catalog $catalog, array $files, array $requestedPageCounts, string $requestedTitle): array
    {
        $files = array_values(array_filter($files, static fn (mixed $file): bool => $file instanceof UploadedFile));
        if ([] === $files) {
            throw new \InvalidArgumentException('Sélectionnez au moins un fichier PDF.');
        }
        if (count($files) > self::MAX_PDF_FILES) {
            throw new \InvalidArgumentException(sprintf('Vous pouvez importer au maximum %d PDF à la fois.', self::MAX_PDF_FILES));
        }

        $pageCounts = [];
        foreach ($files as $index => $file) {
            $this->validatePdf($file);
            $requestedPageCount = (int) ($requestedPageCounts[$index] ?? 0);
            $pageCount = $requestedPageCount > 0 ? $requestedPageCount : $this->detectPdfPageCount($file);
            if ($pageCount < 1 || $pageCount > self::MAX_PDF_PAGES) {
                throw new \InvalidArgumentException(sprintf('Le PDF « %s » doit contenir entre 1 et %d pages.', $this->originalFilename($file), self::MAX_PDF_PAGES));
            }
            $pageCounts[$index] = $pageCount;
        }

        $position = $this->pages->nextPosition($catalog);
        $totalPages = 0;
        $storedPaths = [];

        try {
            foreach ($files as $index => $file) {
                $pageCount = $pageCounts[$index];
                $pdfPath = $this->storePdf($file);
                $storedPaths[] = $pdfPath;
                $originalName = $this->originalFilename($file);
                $fallbackTitle = pathinfo($originalName, PATHINFO_FILENAME);
                $baseTitle = 1 === count($files) ? trim($requestedTitle) : '';
                $baseTitle = mb_substr('' !== $baseTitle ? $baseTitle : ('' !== trim($fallbackTitle) ? $fallbackTitle : 'Catalogue'), 0, 150);

                for ($number = 1; $number <= $pageCount; ++$number) {
                    $title = 1 === $pageCount ? $baseTitle : sprintf('%s · page %d', $baseTitle, $number);
                    $page = (new CatalogPage($catalog, $position++))
                        ->setTitle(mb_substr($title, 0, 180))
                        ->setPdfPath($pdfPath)
                        ->setPdfOriginalName($originalName)
                        ->setPdfPage($number);
                    $this->entityManager->persist($page);
                    ++$totalPages;
                }
            }

            $catalog->touch();
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                $this->deleteUpload($storedPath);
            }

            throw $exception;
        }

        return ['documents' => count($files), 'pages' => $totalPages];
    }

    /** @param array<int, mixed> $submittedIds */
    public function reorder(Catalog $catalog, array $submittedIds): void
    {
        $orderedPages = $this->pages->findForCatalog($catalog);
        $submittedIds = array_values(array_unique(array_filter(array_map(
            static fn (mixed $id): int => is_numeric($id) ? (int) $id : 0,
            $submittedIds,
        ))));
        $currentIds = array_map(static fn (CatalogPage $page): int => (int) $page->getId(), $orderedPages);
        $sortedSubmittedIds = $submittedIds;
        $sortedCurrentIds = $currentIds;
        sort($sortedSubmittedIds);
        sort($sortedCurrentIds);
        if ($sortedSubmittedIds !== $sortedCurrentIds) {
            throw new \InvalidArgumentException('L’ordre des pages est incomplet ou invalide. Rechargez la page puis réessayez.');
        }

        $pagesById = [];
        foreach ($orderedPages as $page) {
            $pagesById[(int) $page->getId()] = $page;
        }
        foreach ($submittedIds as $index => $id) {
            $pagesById[$id]->setPosition($index + 1)->touch();
        }

        $catalog->touch();
        $this->entityManager->flush();
    }

    public function updatePage(
        CatalogPage $page,
        string $title,
        string $linkUrl,
        bool $openInNewTab,
        bool $enabled,
        ?UploadedFile $replacement,
    ): void {
        $title = trim($title);
        if ('' === $title || mb_strlen($title) > 180) {
            throw new \InvalidArgumentException('Le titre de la page doit contenir entre 1 et 180 caractères.');
        }

        $oldImage = $page->getImagePath();
        $oldPdf = $page->getPdfPath();
        if ($replacement instanceof UploadedFile) {
            if ($this->isPdf($replacement)) {
                $originalName = $this->originalFilename($replacement);
                $page->setImagePath(null)->setPdfPath($this->storePdf($replacement))->setPdfOriginalName($originalName)->setPdfPage(1);
            } else {
                $page->setImagePath($this->storeImage($replacement))->setPdfPath(null)->setPdfOriginalName(null)->setPdfPage(null);
            }
        }

        $page
            ->setTitle($title)
            ->setLinkUrl($this->normaliseLink($linkUrl))
            ->setOpenLinkInNewTab($openInNewTab)
            ->setEnabled($enabled);
        $page->touch();
        $page->getCatalog()->touch();
        $this->entityManager->flush();
        $this->deleteUnusedMedia($oldImage, $oldPdf);
    }

    public function move(CatalogPage $page, string $direction): void
    {
        $orderedPages = $this->pages->findForCatalog($page->getCatalog());
        $index = array_search($page, $orderedPages, true);
        if (false === $index) {
            return;
        }

        $targetIndex = 'up' === $direction ? $index - 1 : $index + 1;
        if (!isset($orderedPages[$targetIndex])) {
            return;
        }

        $target = $orderedPages[$targetIndex];
        $position = $page->getPosition();
        $page->setPosition($target->getPosition())->touch();
        $target->setPosition($position)->touch();
        $page->getCatalog()->touch();
        $this->entityManager->flush();
    }

    public function delete(CatalogPage $page): void
    {
        $catalog = $page->getCatalog();
        $oldImage = $page->getImagePath();
        $oldPdf = $page->getPdfPath();
        $this->entityManager->remove($page);
        $this->entityManager->flush();

        foreach ($this->pages->findForCatalog($catalog) as $index => $remainingPage) {
            $remainingPage->setPosition($index + 1);
        }
        $catalog->touch();
        $this->entityManager->flush();
        $this->deleteUnusedMedia($oldImage, $oldPdf);
    }

    private function storeImage(UploadedFile $file): string
    {
        if (!$file->isValid() || ($file->getSize() ?? 0) > self::MAX_IMAGE_SIZE) {
            throw new \InvalidArgumentException('L’image doit être valide et ne pas dépasser 15 Mo.');
        }

        $contents = file_get_contents($file->getPathname());
        $source = false === $contents ? false : imagecreatefromstring($contents);
        if (false === $source) {
            throw new \InvalidArgumentException('Utilisez une image JPG, PNG ou WebP valide.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, 2400 / $sourceWidth, 3400 / $sourceHeight);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        $directory = $this->uploadDirectory();
        $filename = bin2hex(random_bytes(16)).'.webp';
        $saved = imagewebp($target, $directory.'/'.$filename, 90);
        imagedestroy($source);
        imagedestroy($target);
        if (!$saved) {
            throw new \RuntimeException('L’image ne peut pas être enregistrée.');
        }

        return self::UPLOAD_PREFIX.$filename;
    }

    private function storePdf(UploadedFile $file): string
    {
        $this->validatePdf($file);

        $filename = bin2hex(random_bytes(16)).'.pdf';
        $file->move($this->uploadDirectory(), $filename);

        return self::UPLOAD_PREFIX.$filename;
    }

    private function validatePdf(UploadedFile $file): void
    {
        if (!$file->isValid() || ($file->getSize() ?? 0) > self::MAX_PDF_SIZE || !$this->isPdf($file)) {
            throw new \InvalidArgumentException(sprintf('Le fichier « %s » doit être un PDF valide de 90 Mo maximum.', $this->originalFilename($file)));
        }
    }

    private function isPdf(UploadedFile $file): bool
    {
        $handle = fopen($file->getPathname(), 'rb');
        $signature = false === $handle ? '' : fread($handle, 5);
        if (is_resource($handle)) {
            fclose($handle);
        }

        return '%PDF-' === $signature;
    }

    private function originalFilename(UploadedFile $file): string
    {
        $filename = basename(str_replace('\\', '/', $file->getClientOriginalName()));

        return mb_substr('' !== trim($filename) ? $filename : 'document.pdf', 0, 255);
    }

    private function detectPdfPageCount(UploadedFile $file): int
    {
        $contents = file_get_contents($file->getPathname());
        if (false === $contents) {
            return 0;
        }

        preg_match_all('/\/Type\s*\/Page\b/', $contents, $matches);

        return count($matches[0]);
    }

    private function normaliseLink(string $link): ?string
    {
        $link = trim(str_replace(["\r", "\n"], '', $link));
        if ('' === $link) {
            return null;
        }
        if (mb_strlen($link) > 2048) {
            throw new \InvalidArgumentException('Le lien de redirection est trop long.');
        }
        if (str_starts_with($link, '/') && !str_starts_with($link, '//')) {
            return $link;
        }
        if (preg_match('/^(mailto:[^\s@]+@[^\s@]+|tel:\+?[0-9 .()-]+)$/i', $link)) {
            return $link;
        }

        $scheme = parse_url($link, PHP_URL_SCHEME);
        if (!filter_var($link, FILTER_VALIDATE_URL) || !in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Utilisez une URL HTTP(S), un lien interne, une adresse e-mail ou un numéro de téléphone.');
        }

        return $link;
    }

    private function uploadDirectory(): string
    {
        $directory = $this->publicDirectory.self::UPLOAD_PREFIX;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Le dossier d’upload du catalogue ne peut pas être créé.');
        }

        return rtrim($directory, '/');
    }

    private function deleteUnusedMedia(?string $imagePath, ?string $pdfPath): void
    {
        if (null !== $imagePath && 0 === $this->pages->countImageReferences($imagePath)) {
            $this->deleteUpload($imagePath);
        }
        if (null !== $pdfPath && 0 === $this->pages->countPdfReferences($pdfPath)) {
            $this->deleteUpload($pdfPath);
        }
    }

    private function deleteUpload(string $path): void
    {
        if (!str_starts_with($path, self::UPLOAD_PREFIX)) {
            return;
        }

        $file = $this->publicDirectory.self::UPLOAD_PREFIX.basename($path);
        if (is_file($file)) {
            unlink($file);
        }
    }
}
