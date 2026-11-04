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

    public function testItListsAllThirtySixPagesOfOnePdf(): void
    {
        $catalog = new Catalog();
        $pages = [
            (new CatalogPage($catalog, 1))->setTitle('Couverture')->setImagePath('/cover.webp'),
            (new CatalogPage($catalog, 2))->setTitle('Notre approche')->setImagePath('/introduction.webp'),
        ];
        for ($number = 1; $number <= 36; ++$number) {
            $pages[] = (new CatalogPage($catalog, $number + 2))
                ->setTitle('Formation '.$number)
                ->setPdfPath('/uploads/catalogue/catalogue.pdf')
                ->setPdfOriginalName('catalogue version finale.pdf')
                ->setPdfPage($number);
        }
        $pages[] = (new CatalogPage($catalog, 39))->setTitle('Contact')->setImagePath('/contact.webp');
        $pages[] = (new CatalogPage($catalog, 40))->setTitle('Merci')->setImagePath('/back.webp');

        $builder = new CatalogContentsBuilder();
        $entries = $builder->build($catalog, $pages);

        self::assertCount(36, $entries);
        self::assertCount(2, $builder->paginate($entries));
        self::assertSame(range(1, 36), array_column($entries, 'pdfPage'));
        self::assertSame(range(4, 39), array_column($entries, 'pageIndex'));
        self::assertSame('Formation 1', $entries[0]['label']);
        self::assertSame('Formation 36', $entries[35]['label']);
        self::assertSame(40, $entries[35]['pageNumber']);
    }

    public function testItMixesSinglePageAndMultipagePdfsWithIndependentLabels(): void
    {
        $catalog = new Catalog();
        $pages = [(new CatalogPage($catalog, 1))->setTitle('Couverture')->setImagePath('/cover.webp')];
        foreach ([['/first.pdf', 1, 'Première'], ['/multi.pdf', 1, 'Deuxième'], ['/multi.pdf', 2, 'Troisième'], ['/last.pdf', 1, 'Dernière']] as $index => [$path, $number, $title]) {
            $pages[] = (new CatalogPage($catalog, $index + 2))->setTitle($title)->setPdfPath($path)->setPdfPage($number);
        }
        $firstKey = hash('sha256', '/multi.pdf');
        $secondKey = hash('sha256', '/multi.pdf#page=2');
        $catalog->setContentsLabels([$firstKey => 'Titre existant', $secondKey => 'Titre personnalisé']);
        $catalog->setContentsOrder([$secondKey, $firstKey]);

        $entries = (new CatalogContentsBuilder())->build($catalog, $pages);

        self::assertCount(4, $entries);
        self::assertSame(['Titre personnalisé', 'Titre existant', 'Première', 'Dernière'], array_column($entries, 'label'));
        self::assertSame([4, 3, 2, 5], array_column($entries, 'pageIndex'));
        self::assertSame([2, 1, 1, 1], array_column($entries, 'pdfPage'));
    }

    public function testAPageKeepsItsLabelWhenOtherPagesOfItsPdfAreDeleted(): void
    {
        $catalog = new Catalog();
        $key = hash('sha256', '/multi.pdf#page=2');
        $catalog->setContentsLabels([$key => 'Titre conservé']);
        $page = (new CatalogPage($catalog, 1))->setTitle('Deuxième')->setPdfPath('/multi.pdf')->setPdfPage(2);

        $entries = (new CatalogContentsBuilder())->build($catalog, [$page]);

        self::assertCount(1, $entries);
        self::assertSame($key, $entries[0]['key']);
        self::assertSame('Titre conservé', $entries[0]['label']);
        self::assertSame(0, $entries[0]['pageIndex']);
    }
}
