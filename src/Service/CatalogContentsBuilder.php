<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Catalog;
use App\Entity\CatalogPage;

final class CatalogContentsBuilder
{
    private const ENTRIES_PER_PAGE = 18;

    /**
     * @param list<CatalogPage> $pages
     *
     * @return list<array{key: string, label: string, generatedLabel: string, filename: string, pdfPage: int, pageIndex: int, pageNumber: int, pageCount: int, position: int}>
     */
    public function build(Catalog $catalog, array $pages): array
    {
        $documentPageCounts = [];
        foreach ($pages as $page) {
            if (null !== $page->getPdfPath()) {
                $documentPageCounts[$page->getPdfPath()] = ($documentPageCounts[$page->getPdfPath()] ?? 0) + 1;
            }
        }

        $entries = [];
        $seen = [];
        $manualLabels = $catalog->getContentsLabels();
        foreach ($pages as $index => $page) {
            $pdfPath = $page->getPdfPath();
            if (null === $pdfPath) {
                continue;
            }

            $pdfPage = $page->getPdfPage() ?? 1;
            $key = hash('sha256', $pdfPath.($pdfPage > 1 ? '#page='.$pdfPage : ''));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $filename = $page->getPdfOriginalName() ?? basename($pdfPath);
            $generatedLabel = trim($page->getTitle());
            if ('' === $generatedLabel) {
                $generatedLabel = $this->generatedLabel($filename, '');
            }
            $manualLabel = $manualLabels[$key] ?? null;
            $label = is_string($manualLabel) && '' !== trim($manualLabel) ? trim($manualLabel) : $generatedLabel;

            $entries[] = [
                'key' => $key,
                'label' => $label,
                'generatedLabel' => $generatedLabel,
                'filename' => $filename,
                'pdfPage' => $pdfPage,
                'pageIndex' => $index,
                'pageNumber' => $index + 1,
                'pageCount' => $documentPageCounts[$pdfPath],
                'position' => count($entries) + 1,
            ];
        }

        $contentsPageCount = (int) ceil(count($entries) / self::ENTRIES_PER_PAGE);
        foreach ($entries as &$entry) {
            if ($entry['pageIndex'] > 0) {
                $entry['pageIndex'] += $contentsPageCount;
                $entry['pageNumber'] += $contentsPageCount;
            }
        }
        unset($entry);

        $manualPositions = array_flip(array_values(array_filter(
            $catalog->getContentsOrder(),
            static fn (mixed $key): bool => is_string($key),
        )));
        usort($entries, static function (array $left, array $right) use ($manualPositions): int {
            $leftPosition = $manualPositions[$left['key']] ?? PHP_INT_MAX;
            $rightPosition = $manualPositions[$right['key']] ?? PHP_INT_MAX;

            return $leftPosition <=> $rightPosition ?: $left['position'] <=> $right['position'];
        });
        foreach ($entries as $index => &$entry) {
            $entry['position'] = $index + 1;
        }
        unset($entry);

        return $entries;
    }

    /**
     * @param list<array{key: string, label: string, generatedLabel: string, filename: string, pdfPage: int, pageIndex: int, pageNumber: int, pageCount: int, position: int}> $entries
     *
     * @return list<list<array{key: string, label: string, generatedLabel: string, filename: string, pdfPage: int, pageIndex: int, pageNumber: int, pageCount: int, position: int}>>
     */
    public function paginate(array $entries): array
    {
        return array_chunk($entries, self::ENTRIES_PER_PAGE);
    }

    private function generatedLabel(string $filename, string $fallback): string
    {
        $label = pathinfo($filename, PATHINFO_FILENAME);
        $label = preg_replace('/[_-]+/u', ' ', $label) ?? $label;
        $label = preg_replace('/\s+/u', ' ', $label) ?? $label;
        $label = trim($label);

        if ('' === $label || preg_match('/^[a-f0-9]{24,}$/i', $label)) {
            $label = trim(explode(' · page ', $fallback, 2)[0]);
        }

        return '' !== $label ? $label : 'Document PDF';
    }
}
