<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OnlineVisitor;
use App\Repository\OnlineVisitorRepository;

final readonly class OnlineVisitorAnalytics
{
    public const ONLINE_WINDOW_SECONDS = 120;

    public function __construct(private OnlineVisitorRepository $visitors)
    {
    }

    public function currentCount(): int
    {
        return $this->visitors->countActiveSince($this->onlineSince());
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $now = new \DateTimeImmutable();
        $activeVisitors = $this->visitors->findActiveSince($this->onlineSince($now));
        $recentVisitors = $this->visitors->findSeenSince($now->modify('-60 minutes'));
        $onlineCount = count($activeVisitors);
        $averageSeconds = 0;

        if ($onlineCount > 0) {
            $averageSeconds = (int) round(array_sum(array_map(
                static fn (OnlineVisitor $visitor): int => max(0, $now->getTimestamp() - $visitor->getFirstSeenAt()->getTimestamp()),
                $activeVisitors,
            )) / $onlineCount);
        }

        return [
            'generatedAt' => $now->format(DATE_ATOM),
            'onlineCount' => $onlineCount,
            'activeLast15' => $this->visitors->countActiveSince($now->modify('-15 minutes')),
            'sessionsToday' => $this->visitors->countSessionsStartedSince($now->setTime(0, 0)),
            'averageSession' => $this->formatDuration($averageSeconds),
            'devices' => $this->devices($activeVisitors),
            'pages' => $this->pages($activeVisitors),
            'sources' => $this->sources($activeVisitors),
            'visitors' => array_map(fn (OnlineVisitor $visitor): array => $this->visitor($visitor), $activeVisitors),
            'timeline' => $this->timeline($recentVisitors, $now),
        ];
    }

    private function onlineSince(?\DateTimeImmutable $now = null): \DateTimeImmutable
    {
        return ($now ?? new \DateTimeImmutable())->modify('-'.self::ONLINE_WINDOW_SECONDS.' seconds');
    }

    /** @param list<OnlineVisitor> $visitors
     *  @return list<array{key: string, label: string, count: int, share: int}>
     */
    private function devices(array $visitors): array
    {
        $labels = ['desktop' => 'Ordinateur', 'mobile' => 'Mobile', 'tablet' => 'Tablette'];
        $counts = array_fill_keys(array_keys($labels), 0);
        foreach ($visitors as $visitor) {
            ++$counts[$visitor->getDevice()];
        }

        $total = max(1, count($visitors));

        return array_map(static fn (string $key, string $label): array => [
            'key' => $key,
            'label' => $label,
            'count' => $counts[$key],
            'share' => (int) round($counts[$key] / $total * 100),
        ], array_keys($labels), array_values($labels));
    }

    /** @param list<OnlineVisitor> $visitors
     *  @return list<array{path: string, title: string, count: int, share: int}>
     */
    private function pages(array $visitors): array
    {
        $pages = [];
        foreach ($visitors as $visitor) {
            $path = $visitor->getPagePath();
            $pages[$path] ??= ['path' => $path, 'title' => $this->cleanTitle($visitor->getPageTitle(), $path), 'count' => 0];
            ++$pages[$path]['count'];
        }

        usort($pages, static fn (array $left, array $right): int => $right['count'] <=> $left['count']);
        $maximum = max(1, ...array_column($pages ?: [['count' => 1]], 'count'));

        return array_map(static fn (array $page): array => $page + [
            'share' => (int) round($page['count'] / $maximum * 100),
        ], array_slice($pages, 0, 6));
    }

    /** @param list<OnlineVisitor> $visitors
     *  @return list<array{label: string, count: int}>
     */
    private function sources(array $visitors): array
    {
        $sources = [];
        foreach ($visitors as $visitor) {
            $label = $visitor->getReferrerHost() ?? 'Accès direct';
            $sources[$label] = ($sources[$label] ?? 0) + 1;
        }
        arsort($sources);

        return array_map(static fn (string $label, int $count): array => [
            'label' => $label,
            'count' => $count,
        ], array_keys($sources), array_values($sources));
    }

    /** @return array<string, int|string> */
    private function visitor(OnlineVisitor $visitor): array
    {
        return [
            'id' => mb_strtoupper(mb_substr($visitor->getVisitorKey(), 0, 6)),
            'device' => $visitor->getDevice(),
            'browser' => $visitor->getBrowser(),
            'pagePath' => $visitor->getPagePath(),
            'pageTitle' => $this->cleanTitle($visitor->getPageTitle(), $visitor->getPagePath()),
            'source' => $visitor->getReferrerHost() ?? 'Accès direct',
            'pageViews' => $visitor->getPageViews(),
            'firstSeenAt' => $visitor->getFirstSeenAt()->format(DATE_ATOM),
            'lastSeenAt' => $visitor->getLastSeenAt()->format(DATE_ATOM),
        ];
    }

    /** @param list<OnlineVisitor> $visitors
     *  @return list<array{label: string, count: int, share: int}>
     */
    private function timeline(array $visitors, \DateTimeImmutable $now): array
    {
        $points = [];
        for ($index = 11; $index >= 0; --$index) {
            $end = $now->modify(sprintf('-%d minutes', $index * 5));
            $start = $end->modify('-5 minutes');
            $count = count(array_filter($visitors, static fn (OnlineVisitor $visitor): bool =>
                $visitor->getFirstSeenAt() <= $end && $visitor->getLastSeenAt() >= $start
            ));
            $points[] = ['label' => $end->format('H:i'), 'count' => $count];
        }

        $maximum = max(1, ...array_column($points, 'count'));

        return array_map(static fn (array $point): array => $point + [
            'share' => max(8, (int) round($point['count'] / $maximum * 100)),
        ], $points);
    }

    private function cleanTitle(string $title, string $path): string
    {
        $title = trim(preg_replace('/\s*[·|]\s*Les Consultants.*$/u', '', $title) ?? $title);

        return '' !== $title ? $title : ('/' === $path ? 'Accueil' : $path);
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.' s';
        }

        $minutes = intdiv($seconds, 60);

        return $minutes < 60 ? $minutes.' min' : intdiv($minutes, 60).' h '.($minutes % 60).' min';
    }
}
