<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PageView> */
final class PageViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageView::class);
    }

    /** @return array<string, int> */
    public function visitsByDaySince(\DateTimeImmutable $since): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT DATE(visited_at) AS visit_day, COUNT(DISTINCT visitor_key) AS visit_count FROM visitor_page_view WHERE visited_at >= :since GROUP BY DATE(visited_at) ORDER BY visit_day ASC',
            ['since' => $since],
            ['since' => Types::DATETIME_IMMUTABLE],
        );

        $visits = [];
        foreach ($rows as $row) {
            $visits[(string) $row['visit_day']] = (int) $row['visit_count'];
        }

        return $visits;
    }

    /** @return list<array{path: string, title: string, views: int}> */
    public function popularPagesSince(\DateTimeImmutable $since, int $limit = 5): array
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $rows = $builder
            ->select('page_path AS path', 'MAX(page_title) AS title', 'COUNT(*) AS view_count')
            ->from('visitor_page_view')
            ->where('visited_at >= :since')
            ->setParameter('since', $since, Types::DATETIME_IMMUTABLE)
            ->groupBy('page_path')
            ->orderBy('view_count', 'DESC')
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'path' => (string) $row['path'],
            'title' => (string) $row['title'],
            'views' => (int) $row['view_count'],
        ], $rows);
    }

    public function countUniqueVisitorsSince(\DateTimeImmutable $since): int
    {
        return (int) $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT COUNT(DISTINCT visitor_key) FROM visitor_page_view WHERE visited_at >= :since',
            ['since' => $since],
            ['since' => Types::DATETIME_IMMUTABLE],
        );
    }

    public function removeBefore(\DateTimeImmutable $before): int
    {
        return $this->createQueryBuilder('pageView')
            ->delete()
            ->andWhere('pageView.visitedAt < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
}
