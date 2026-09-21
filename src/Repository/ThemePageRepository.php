<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ThemePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ThemePage> */
final class ThemePageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemePage::class);
    }

    public function findBySlug(string $slug): ?ThemePage
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
