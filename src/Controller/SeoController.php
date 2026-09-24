<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SitemapProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SeoController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'], defaults: ['_format' => 'xml'])]
    public function sitemap(Request $request, SitemapProvider $sitemap): Response
    {
        $data = $sitemap->provide();
        $response = $this->render('seo/sitemap.xml.twig', ['entries' => $data['entries']]);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');
        $response->setPublic();
        $response->setMaxAge(900);
        $response->setSharedMaxAge(3600);

        if ($data['lastModified'] instanceof \DateTimeImmutable) {
            $response->setLastModified($data['lastModified']);
            $response->isNotModified($request);
        }

        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $response = $this->render('seo/robots.txt.twig');
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}
