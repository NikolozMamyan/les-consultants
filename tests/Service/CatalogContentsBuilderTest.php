<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Catalog;
use App\Entity\CatalogPage;
use App\Service\CatalogContentsBuilder;
use PHPUnit\Framework\TestCase;

final class CatalogContentsBuilderTest extends TestCase
{
    public function testItPaginatesAndOffsetsThirtyFourDocuments(): void
    {
        $catalog = new Catalog();
        $pages = [(new CatalogPage($catalog, 1))->setTitle('Couverture')->setImagePath('/cover.webp')];
        for ($number = 1; $number <= 34; ++$number) {
            $pages[] = (new CatalogPage($catalog, $number + 1))
                ->setTitle('Formation '.$number)
                ->setPdfPath('/uploads/catalogue/document-'.$number.'.pdf')
                ->setPdfOriginalName('Formation-'.$number.'.pdf')
                ->setPdfPage(1);
        }

        $builder = new CatalogContentsBuilder();
        $entries = $builder->build($catalog, $pages);
        $contentsPages = $builder->paginate($entries);

        self::assertCount(34, $entries);
        self::assertCount(2, $contentsPages);
        self::assertCount(18, $contentsPages[0]);
        self::assertCount(16, $contentsPages[1]);
        self::assertSame(3, $entries[0]['pageIndex']);
        self::assertSame(36, $entries[33]['pageIndex']);
    }

    public function testItAppliesTheSavedContentsOrder(): void
    {
        $catalog = new Catalog();
        $first = (new CatalogPage($catalog, 1))->setTitle('Première')->setPdfPath('/first.pdf')->setPdfOriginalName('Première.pdf')->setPdfPage(1);
        $second = (new CatalogPage($catalog, 2))->setTitle('Seconde')->setPdfPath('/second.pdf')->setPdfOriginalName('Seconde.pdf')->setPdfPage(1);
        $catalog->setContentsOrder([hash('sha256', '/second.pdf'), hash('sha256', '/first.pdf')]);

        $entries = (new CatalogContentsBuilder())->build($catalog, [$first, $second]);

        self::assertSame(['Seconde', 'Première'], array_column($entries, 'label'));
        self::assertSame([1, 2], array_column($entries, 'position'));
    }
}
