<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Submission;
use App\Repository\BlogPostRepository;
use App\Repository\PageViewRepository;
use App\Repository\SubmissionRepository;

final readonly class AdminDashboardDataProvider
{
    private const DAY_LABELS = [1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 7 => 'Dim'];

    public function __construct(
        private OnlineVisitorAnalytics $onlineVisitors,
        private SubmissionRepository $submissions,
        private PageViewRepository $pageViews,
        private BlogPostRepository $blogPosts,
    ) {
    }

    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        $now = new \DateTimeImmutable();
        $monthStart = $now->modify('first day of this month')->setTime(0, 0);
        $missionCount = $this->submissions->count(['type' => Submission::TYPE_MISSION]);
        $profileCount = $this->submissions->count(['type' => Submission::TYPE_PROFILE]);
        $submissionCount = $missionCount + $profileCount;
        $processedCount = $this->submissions->countProcessed();
        $processingRate = $submissionCount > 0 ? (int) round($processedCount / $submissionCount * 100) : 0;
        $traffic = $this->traffic($now);
        $popularPages = $this->popularPages($now);

        return [
            'stats' => [
                ['label' => 'Utilisateurs en ligne', 'value' => (string) $this->onlineVisitors->currentCount(), 'trend' => 'En direct', 'tone' => 'live', 'icon' => 'activity', 'route' => 'admin_online'],
                ['label' => 'Missions déposées', 'value' => (string) $missionCount, 'trend' => $this->submissions->countByTypeSince(Submission::TYPE_MISSION, $monthStart).' ce mois', 'tone' => 'positive', 'icon' => 'briefcase', 'route' => 'admin_submissions', 'routeParams' => ['type' => Submission::TYPE_MISSION]],
                ['label' => 'Profils reçus', 'value' => (string) $profileCount, 'trend' => $this->submissions->countByTypeSince(Submission::TYPE_PROFILE, $monthStart).' ce mois', 'tone' => 'positive', 'icon' => 'people', 'route' => 'admin_submissions', 'routeParams' => ['type' => Submission::TYPE_PROFILE]],
                ['label' => 'Taux de traitement', 'value' => $processingRate.' %', 'trend' => $processedCount.'/'.$submissionCount.' dossiers avancés', 'tone' => 'positive', 'icon' => 'chart', 'route' => 'admin_submissions'],
            ],
            'traffic' => $traffic['points'],
            'trafficTotal' => $traffic['total'],
            'trafficTrend' => $traffic['trend'],
            'trafficTrendTone' => $traffic['tone'],
            'quickStats' => [
                ['label' => 'Demandes à traiter', 'value' => (string) $this->submissions->countPending(), 'detail' => $this->submissions->countOpenMissions().' missions ouvertes'],
                ['label' => 'Articles publiés', 'value' => (string) $this->blogPosts->count(['published' => true]), 'detail' => $this->blogPosts->count(['published' => false]).' brouillons'],
                ['label' => 'Visiteurs aujourd’hui', 'value' => (string) $this->pageViews->countUniqueVisitorsSince($now->setTime(0, 0)), 'detail' => 'Audience réelle du site'],
            ],
            'recentSubmissions' => array_map(fn (Submission $submission): array => [
                'type' => $submission->getTypeLabel(),
                'name' => $submission->getSubject(),
                'contact' => $submission->getOrganization() ?? $submission->getContactName(),
                'date' => $this->relativeDate($submission->getCreatedAt(), $now),
                'status' => $submission->getStatusLabel(),
                'statusTone' => $submission->getStatusTone(),
                'id' => $submission->getId(),
            ], $this->submissions->findRecent()),
            'popularPages' => $popularPages,
        ];
    }

    /** @return array{points: list<array{day: string, value: int, visits: int}>, total: int, trend: string, tone: string} */
    private function traffic(\DateTimeImmutable $now): array
    {
        $start = $now->setTime(0, 0)->modify('-13 days');
        $visitsByDay = $this->pageViews->visitsByDaySince($start);
        $currentTotal = 0;
        $previousTotal = 0;
        $rawPoints = [];

        for ($daysAgo = 13; $daysAgo >= 0; --$daysAgo) {
            $date = $now->setTime(0, 0)->modify(sprintf('-%d days', $daysAgo));
            $visits = $visitsByDay[$date->format('Y-m-d')] ?? 0;
            if ($daysAgo <= 6) {
                $currentTotal += $visits;
                $rawPoints[] = ['date' => $date, 'visits' => $visits];
            } else {
                $previousTotal += $visits;
            }
        }

        $maximum = max(1, ...array_column($rawPoints, 'visits'));
        $points = array_map(static fn (array $point): array => [
            'day' => self::DAY_LABELS[(int) $point['date']->format('N')],
            'value' => 0 === $point['visits'] ? 4 : max(12, (int) round($point['visits'] / $maximum * 100)),
            'visits' => $point['visits'],
        ], $rawPoints);

        if (0 === $previousTotal) {
            $trend = $currentTotal > 0 ? 'Premières visites enregistrées' : 'En attente des premières visites';
            $tone = 'neutral';
        } else {
            $change = ($currentTotal - $previousTotal) / $previousTotal * 100;
            $trend = sprintf('%s%s %% par rapport à la semaine précédente', $change >= 0 ? '+' : '', number_format($change, 1, ',', ' '));
            $tone = $change >= 0 ? 'positive' : 'negative';
        }

        return ['points' => $points, 'total' => $currentTotal, 'trend' => $trend, 'tone' => $tone];
    }

    /** @return list<array{name: string, path: string, views: string, share: int}> */
    private function popularPages(\DateTimeImmutable $now): array
    {
        $pages = $this->pageViews->popularPagesSince($now->modify('-30 days'));
        $maximum = max(1, ...array_column($pages ?: [['views' => 1]], 'views'));

        return array_map(fn (array $page): array => [
            'name' => $this->cleanTitle($page['title'], $page['path']),
            'path' => $page['path'],
            'views' => number_format($page['views'], 0, ',', ' '),
            'share' => max(6, (int) round($page['views'] / $maximum * 100)),
        ], $pages);
    }

    private function relativeDate(\DateTimeImmutable $date, \DateTimeImmutable $now): string
    {
        $seconds = max(0, $now->getTimestamp() - $date->getTimestamp());
        if ($seconds < 60) {
            return 'À l’instant';
        }
        if ($seconds < 3600) {
            return 'Il y a '.intdiv($seconds, 60).' min';
        }
        if ($seconds < 86400) {
            return 'Il y a '.intdiv($seconds, 3600).' h';
        }

        return $date->format('d/m/Y');
    }

    private function cleanTitle(string $title, string $path): string
    {
        $title = trim(preg_replace('/\s*[·|]\s*Les Consultants.*$/u', '', $title) ?? $title);

        return '' !== $title ? $title : ('/' === $path ? 'Accueil' : $path);
    }
}
