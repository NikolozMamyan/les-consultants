<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\VisitorActivityTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisitorActivityController extends AbstractController
{
    #[Route('/activite-visiteur', name: 'app_visitor_activity', methods: ['POST'])]
    public function __invoke(Request $request, VisitorActivityTracker $tracker): JsonResponse
    {
        $origin = $request->headers->get('Origin');
        $originHost = is_string($origin) ? parse_url($origin, PHP_URL_HOST) : null;
        if (is_string($originHost) && $originHost !== $request->getHost()) {
            return $this->json(['message' => 'Origine invalide.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['message' => 'Données invalides.'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($payload)) {
            return $this->json(['message' => 'Données invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $pagePath = $this->pagePath($payload['path'] ?? '/');
        if (str_starts_with($pagePath, '/admin')) {
            return $this->json([], Response::HTTP_NO_CONTENT);
        }

        $pageTitle = mb_substr(trim(strip_tags((string) ($payload['title'] ?? ''))), 0, 180);
        $referrer = isset($payload['referrer']) ? mb_substr((string) $payload['referrer'], 0, 2000) : null;
        $visitorKey = $tracker->record($request, $pagePath, $pageTitle, $referrer);

        $response = $this->json(['recorded' => true]);
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->headers->setCookie(
            Cookie::create(VisitorActivityTracker::COOKIE_NAME)
                ->withValue($visitorKey)
                ->withPath('/')
                ->withSecure($request->isSecure())
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_LAX),
        );

        return $response;
    }

    private function pagePath(mixed $value): string
    {
        $path = parse_url((string) $value, PHP_URL_PATH);
        if (!is_string($path) || !str_starts_with($path, '/')) {
            return '/';
        }

        return mb_substr($path, 0, 255);
    }
}
