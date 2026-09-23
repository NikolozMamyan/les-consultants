<?php

declare(strict_types=1);

namespace App\Twig;

use App\Repository\SubmissionRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AdminSubmissionExtension extends AbstractExtension
{
    public function __construct(private readonly SubmissionRepository $submissions)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('admin_pending_submission_count', $this->pendingCount(...))];
    }

    public function pendingCount(): int
    {
        return $this->submissions->countPending();
    }
}
