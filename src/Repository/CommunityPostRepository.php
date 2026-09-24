<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommunityPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CommunityPost> */
final class CommunityPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommunityPost::class);
    }

    /** @return list<CommunityPost> */
    public function findRecent(int $limit = 8): array
    {
        return $this->createQueryBuilder('post')
            ->orderBy('post.publishedAt', \SortDirection::Descending)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?CommunityPost
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /** @return list<CommunityPost> */
    public function findForSitemap(): array
    {
        return $this->createQueryBuilder('post')
            ->orderBy('post.publishedAt', \SortDirection::Descending)
            ->getQuery()
            ->getResult();
    }
}
