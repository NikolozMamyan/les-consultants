<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OnlineVisitor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<OnlineVisitor> */
final class OnlineVisitorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OnlineVisitor::class);
    }

    public function findOneByVisitorKey(string $visitorKey): ?OnlineVisitor
    {
        return $this->findOneBy(['visitorKey' => $visitorKey]);
    }

    public function countActiveSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('visitor')
            ->select('COUNT(visitor.id)')
            ->andWhere('visitor.lastSeenAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<OnlineVisitor> */
    public function findActiveSince(\DateTimeImmutable $since, int $limit = 100): array
    {
        return $this->createQueryBuilder('visitor')
            ->andWhere('visitor.lastSeenAt >= :since')
            ->setParameter('since', $since)
            ->orderBy('visitor.lastSeenAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return list<OnlineVisitor> */
    public function findSeenSince(\DateTimeImmutable $since, int $limit = 1000): array
    {
        return $this->createQueryBuilder('visitor')
            ->andWhere('visitor.lastSeenAt >= :since')
            ->setParameter('since', $since)
            ->orderBy('visitor.lastSeenAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countSessionsStartedSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('visitor')
            ->select('COUNT(visitor.id)')
            ->andWhere('visitor.firstSeenAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function removeInactiveBefore(\DateTimeImmutable $before): int
    {
        return $this->createQueryBuilder('visitor')
            ->delete()
            ->andWhere('visitor.lastSeenAt < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
}
