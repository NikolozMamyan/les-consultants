<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<BlogPost> */
final class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    /** @return list<BlogPost> */
    public function findPublished(?string $search = null, ?string $category = null): array
    {
        $builder = $this->createQueryBuilder('post')
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->orderBy('post.publishedAt', \SortDirection::Descending);

        if (null !== $search && '' !== $search) {
            $builder
                ->andWhere('LOWER(post.title) LIKE :search OR LOWER(post.excerpt) LIKE :search OR LOWER(post.content) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        if (null !== $category && '' !== $category) {
            $builder
                ->andWhere('post.category = :category')
                ->setParameter('category', $category);
        }

        return $builder->getQuery()->getResult();
    }

    /** @return list<string> */
    public function findPublishedCategories(): array
    {
        return array_column($this->createQueryBuilder('post')
            ->select('DISTINCT post.category AS category')
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->orderBy('post.category', \SortDirection::Ascending)
            ->getQuery()
            ->getArrayResult(), 'category');
    }

    public function findPublishedBySlug(string $slug): ?BlogPost
    {
        return $this->findOneBy(['slug' => $slug, 'published' => true]);
    }

    /** @return list<BlogPost> */
    public function findForAdmin(string $search = '', ?bool $published = null): array
    {
        $builder = $this->createQueryBuilder('post')
            ->orderBy('post.updatedAt', \SortDirection::Descending);

        if ('' !== $search) {
            $builder
                ->andWhere('LOWER(post.title) LIKE :search OR LOWER(post.category) LIKE :search OR LOWER(post.author) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        if (null !== $published) {
            $builder
                ->andWhere('post.published = :published')
                ->setParameter('published', $published);
        }

        return $builder->getQuery()->getResult();
    }

    public function slugExistsForAnotherPost(string $slug, ?int $postId): bool
    {
        $builder = $this->createQueryBuilder('post')
            ->select('COUNT(post.id)')
            ->andWhere('post.slug = :slug')
            ->setParameter('slug', $slug);

        if (null !== $postId) {
            $builder
                ->andWhere('post.id != :id')
                ->setParameter('id', $postId);
        }

        return 0 < (int) $builder->getQuery()->getSingleScalarResult();
    }

    /** @return list<BlogPost> */
    public function findRelated(BlogPost $post, int $limit = 3): array
    {
        return $this->createQueryBuilder('related')
            ->andWhere('related.published = :published')
            ->andWhere('related.id != :id')
            ->andWhere('related.category = :category')
            ->setParameter('published', true)
            ->setParameter('id', $post->getId())
            ->setParameter('category', $post->getCategory())
            ->orderBy('related.publishedAt', \SortDirection::Descending)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
