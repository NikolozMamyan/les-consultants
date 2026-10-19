<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EventRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<EventRegistration> */
final class EventRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRegistration::class);
    }

    /** @return list<EventRegistration> */
    public function findForEvent(string $eventSlug): array
    {
        return $this->findBy(['eventSlug' => $eventSlug], ['createdAt' => 'DESC', 'id' => 'DESC']);
    }
}
