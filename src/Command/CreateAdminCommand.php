<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AdminUserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:admin:create', description: 'Crée le premier super-administrateur du back-office.')]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly AdminUserManager $userManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail du compte')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Nom affiché')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe (sinon demandé de façon masquée)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = mb_strtolower(trim((string) $input->getArgument('email')));
        $name = trim((string) ($input->getOption('name') ?: $email));
        $password = $input->getOption('password');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('L’adresse e-mail est invalide.');

            return Command::INVALID;
        }

        if (null !== $this->users->findOneByEmail($email)) {
            $io->error('Un compte utilise déjà cette adresse e-mail.');

            return Command::FAILURE;
        }

        if (!is_string($password) || '' === $password) {
            $password = $io->askHidden('Mot de passe (12 caractères minimum)');
        }

        if (!is_string($password) || mb_strlen($password) < 12) {
            $io->error('Le mot de passe doit contenir au moins 12 caractères.');

            return Command::INVALID;
        }

        $user = (new User())
            ->setEmail($email)
            ->setDisplayName($name)
            ->setActive(true);
        $this->userManager->save($user, AdminUserManager::ROLE_SUPER_ADMIN, $password);

        $io->success(sprintf('Le super-administrateur %s a été créé.', $email));

        return Command::SUCCESS;
    }
}
