<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ContactRequest;
use App\Dto\MissionRequest;
use App\Entity\Submission;
use App\Repository\SubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SubmissionManager
{
    private const EXPERTISE_LABELS = [
        'compliance' => 'Compliance & réglementation',
        'finance' => 'Finance & fonds',
        'risques' => 'Risques & gouvernance',
        'juridique' => 'Juridique & réglementaire',
        'digital' => 'Transformation & IT',
        'formation' => 'Formation',
        'autre' => 'Autre expertise',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubmissionRepository $submissions,
    ) {
    }

    public function recordMission(MissionRequest $request): Submission
    {
        $submission = (new Submission(
            Submission::TYPE_MISSION,
            (string) $request->missionTitle,
            (string) $request->contactName,
            (string) $request->email,
            (string) $request->description,
        ))
            ->setOrganization($request->company)
            ->setPhone($request->phone)
            ->setExpertise($request->expertise)
            ->setStartDate($request->startDate)
            ->setDuration($request->duration);

        $this->save($submission);

        return $submission;
    }

    public function recordProfile(ContactRequest $request): ?Submission
    {
        if ('consultant' !== $request->profile) {
            return null;
        }

        $expertise = self::EXPERTISE_LABELS[(string) $request->subject] ?? (string) $request->subject;
        $submission = (new Submission(
            Submission::TYPE_PROFILE,
            (string) $request->name,
            (string) $request->name,
            (string) $request->email,
            (string) $request->message,
        ))
            ->setPhone($request->phone)
            ->setExpertise($expertise);

        $this->save($submission);

        return $submission;
    }

    /** @return array<string, mixed> */
    public function adminData(?string $type, string $search): array
    {
        return [
            'submissions' => $this->submissions->findForAdmin($type, $search),
            'counts' => [
                'all' => $this->submissions->count([]),
                Submission::TYPE_MISSION => $this->submissions->count(['type' => Submission::TYPE_MISSION]),
                Submission::TYPE_PROFILE => $this->submissions->count(['type' => Submission::TYPE_PROFILE]),
            ],
            'stats' => [
                ['label' => 'Nouveaux dépôts', 'value' => $this->submissions->countCreatedSince(new \DateTimeImmutable('today')), 'detail' => 'Aujourd’hui'],
                ['label' => 'Missions ouvertes', 'value' => $this->submissions->countOpenMissions(), 'detail' => 'À suivre'],
                ['label' => 'Profils à qualifier', 'value' => $this->submissions->countNewProfiles(), 'detail' => 'Nouveaux talents'],
                ['label' => 'Dossiers à traiter', 'value' => $this->submissions->countPending(), 'detail' => 'Action requise'],
            ],
            'selectedType' => $type,
            'search' => $search,
        ];
    }

    public function updateStatus(Submission $submission, string $status): void
    {
        $submission->setStatus($status);
        $this->entityManager->flush();
    }

    private function save(Submission $submission): void
    {
        $this->entityManager->persist($submission);
        $this->entityManager->flush();
    }
}
