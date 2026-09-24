<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OnlineVisitor;
use App\Entity\PageView;
use App\Repository\OnlineVisitorRepository;
use App\Repository\PageViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class VisitorActivityTracker
{
    public const COOKIE_NAME = 'lc_visitor';

    public function __construct(
        private OnlineVisitorRepository $visitors,
        private PageViewRepository $pageViews,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function record(Request $request, string $pagePath, string $pageTitle, ?string $referrer): string
    {
        $visitorKey = $this->visitorKey($request);
        $now = new \DateTimeImmutable();
        $userAgent = mb_substr($request->headers->get('User-Agent', ''), 0, 500);
        $device = $this->detectDevice($userAgent);
        $browser = $this->detectBrowser($userAgent);
        $referrerHost = $this->externalReferrerHost($request, $referrer);
        $visitor = $this->visitors->findOneByVisitorKey($visitorKey);
        $pageChanged = true;

        if (!$visitor instanceof OnlineVisitor) {
            $visitor = new OnlineVisitor($visitorKey, $pagePath, $pageTitle, $device, $browser, $referrerHost);
            $this->entityManager->persist($visitor);
        } else {
            $pageChanged = $visitor->recordActivity($pagePath, $pageTitle, $device, $browser, $referrerHost, $now);
        }

        if ($pageChanged) {
            $this->entityManager->persist(new PageView($visitorKey, $pagePath, $pageTitle, $device, $referrerHost, $now));
        }

        $this->entityManager->flush();

        if (100 === random_int(1, 100)) {
            $this->visitors->removeInactiveBefore($now->modify('-30 days'));
            $this->pageViews->removeBefore($now->modify('-13 months'));
        }

        return $visitorKey;
    }

    private function visitorKey(Request $request): string
    {
        $visitorKey = $request->cookies->get(self::COOKIE_NAME);

        return is_string($visitorKey) && 1 === preg_match('/^[a-f0-9]{32}$/', $visitorKey)
            ? $visitorKey
            : bin2hex(random_bytes(16));
    }

    private function detectDevice(string $userAgent): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/Mobile|Android|iPhone|IEMobile|Opera Mini/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'OPR/'), str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Navigateur inconnu',
        };
    }

    private function externalReferrerHost(Request $request, ?string $referrer): ?string
    {
        if (null === $referrer || '' === trim($referrer)) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        if (!is_string($host) || '' === $host || $host === $request->getHost()) {
            return null;
        }

        return mb_substr(mb_strtolower(preg_replace('/^www\./', '', $host) ?? $host), 0, 120);
    }
}
