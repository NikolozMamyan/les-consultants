<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogController extends AbstractController
{
    private const RBA_SLUG = 'la-risk-based-approach-rba-un-principe-fondamental-souvent-mal-compris';

    #[Route('/blog', name: 'app_blog_index', methods: ['GET'])]
    public function index(Request $request, BlogPostRepository $posts): Response
    {
        $search = mb_substr(trim($request->query->getString('q')), 0, 100);
        $category = mb_substr(trim($request->query->getString('category')), 0, 80);
        $categories = $posts->findPublishedCategories();
        if ('' !== $category && !in_array($category, $categories, true)) {
            $category = '';
        }

        $results = $posts->findPublished($search, $category);
        $featured = '' === $search && '' === $category ? array_shift($results) : null;

        return $this->render('blog/index.html.twig', [
            'page' => 'blog',
            'posts' => $results,
            'featured' => $featured,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $category,
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_blog_show', methods: ['GET'])]
    public function show(string $slug, BlogPostRepository $posts): Response
    {
        $post = $posts->findPublishedBySlug($slug);
        if (null === $post) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->getTitle(),
            'description' => $post->getMetaDescription(),
            'datePublished' => $post->getPublishedAt()->format(DATE_ATOM),
            'dateModified' => $post->getUpdatedAt()->format(DATE_ATOM),
            'author' => ['@type' => 'Organization', 'name' => $post->getAuthor()],
            'publisher' => ['@type' => 'Organization', 'name' => 'Les Consultants'],
            'mainEntityOfPage' => $this->generateUrl(
                'app_blog_show',
                ['slug' => $post->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ];

        return $this->render('blog/show.html.twig', [
            'page' => 'blog',
            'post' => $post,
            'relatedPosts' => $posts->findRelated($post),
            'structuredData' => $structuredData,
        ]);
    }

    #[Route(
        '/{slug}/',
        name: 'app_blog_legacy',
        requirements: ['slug' => 'freelances-entreprises-une-nouvelle-maniere-de-collaborer-avec-les-consultants|mise-en-conformite-des-fonds-non-regules-raif-aif-la-fin-de-la-tolerance-administrative|obligations-de-formation-au-luxembourg-un-enjeu-strategique-de-conformite|screening-des-clients-des-third-parties-et-des-titres-sanctionnes-comment-remplir-son-obligation-de-controle-continu|risque-de-reputation-un-enjeu-sous-estime-aux-consequences-majeures|monaco-place-sur-la-liste-grise-du-gafi-un-signal-dalerte-pour-les-places-financieres|digitalisation-des-services-fiduciaires-un-levier-pour-la-conformite-et-lefficacite-au-luxembourg|luxembourg-actualisation-de-levaluation-nationale-des-risques-de-blanchiment-enr-2025|marche-du-conseil-et-du-recrutement-en-mai-2025-entre-resilience-et-opportunites-au-luxembourg|la-risk-based-approach-rba-un-principe-fondamental-souvent-mal-compris|risk-based-approach|compliance-monitoring-plan|formation-professionnel-incontournable'],
        methods: ['GET'],
        priority: -10,
    )]
    public function legacy(string $slug): Response
    {
        return $this->redirectToRoute('app_blog_show', [
            'slug' => 'risk-based-approach' === $slug ? self::RBA_SLUG : $slug,
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}
