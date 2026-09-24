<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CommunityFeedSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:community-feed:sync', description: 'Synchronise les publications de la plateforme e-learning dans le site vitrine.')]
final class SyncCommunityFeedCommand extends Command
{
    public function __construct(private readonly CommunityFeedSynchronizer $synchronizer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $this->synchronizer->synchronize();
        $io->success(sprintf('%d publication%s synchronisée%s.', $count, $count > 1 ? 's' : '', $count > 1 ? 's' : ''));

        return Command::SUCCESS;
    }
}
