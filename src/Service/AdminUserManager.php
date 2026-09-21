<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminUserManager
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function save(User $user, string $role, ?string $plainPassword = null): void
    {
        if (!in_array($role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true)) {
            throw new \InvalidArgumentException('Le rôle sélectionné est invalide.');
        }

        if (null === $user->getId() && (null === $plainPassword || '' === $plainPassword)) {
            throw new \InvalidArgumentException('Un mot de passe est requis pour créer le compte.');
        }

        $user->setRoles([$role]);
        if (null !== $plainPassword && '' !== $plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $user->touch();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
