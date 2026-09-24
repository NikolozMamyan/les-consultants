<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Submission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Submission> */
final class SubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Submission::class);
    }

    /** @return list<Submission> */
    public function findForAdmin(?string $type = null, string $search = ''): array
    {
        $builder = $this->createQueryBuilder('submission')
            ->orderBy('submission.createdAt', 'DESC');

        if (null !== $type) {
            $builder
                ->andWhere('submission.type = :type')
                ->setParameter('type', $type);
        }

        if ('' !== $search) {
            $builder
                ->andWhere('LOWER(submission.subject) LIKE :search OR LOWER(submission.contactName) LIKE :search OR LOWER(submission.organization) LIKE :search OR LOWER(submission.email) LIKE :search OR LOWER(submission.expertise) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        return $builder->getQuery()->getResult();
    }

    public function countCreatedSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('submission')
            ->select('COUNT(submission.id)')
            ->andWhere('submission.createdAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOpenMissions(): int
    {
        return (int) $this->createQueryBuilder('submission')
            ->select('COUNT(submission.id)')
            ->andWhere('submission.type = :type')
            ->andWhere('submission.status != :closed')
            ->setParameter('type', Submission::TYPE_MISSION)
            ->setParameter('closed', Submission::STATUS_CLOSED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNewProfiles(): int
    {
        return $this->count([
            'type' => Submission::TYPE_PROFILE,
            'status' => Submission::STATUS_NEW,
        ]);
    }

    public function countPending(): int
    {
        return $this->count(['status' => Submission::STATUS_NEW]);
    }

    public function countByTypeSince(string $type, \DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('submission')
            ->select('COUNT(submission.id)')
            ->andWhere('submission.type = :type')
            ->andWhere('submission.createdAt >= :since')
            ->setParameter('type', $type)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countProcessed(): int
    {
        return (int) $this->createQueryBuilder('submission')
            ->select('COUNT(submission.id)')
            ->andWhere('submission.status != :new')
            ->setParameter('new', Submission::STATUS_NEW)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<Submission> */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('submission')
            ->orderBy('submission.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
