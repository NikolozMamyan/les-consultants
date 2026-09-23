<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use App\Form\BlogPostType;
use App\Repository\BlogPostRepository;
use App\Service\BlogPostManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/blog', name: 'admin_blog')]
final class BlogController extends AbstractController
{
    public function __construct(
        private readonly BlogPostRepository $posts,
        private readonly BlogPostManager $manager,
    ) {
    }

    #[Route('', name: '', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = mb_substr(trim($request->query->getString('q')), 0, 120);
        $status = $request->query->getString('status');
        $published = match ($status) {
            'published' => true,
            'draft' => false,
            default => null,
        };

        return $this->render('admin/blog.html.twig', [
            'adminSection' => 'blog',
            'posts' => $this->posts->findForAdmin($search, $published),
            'counts' => [
                'all' => $this->posts->count([]),
                'published' => $this->posts->count(['published' => true]),
                'draft' => $this->posts->count(['published' => false]),
                'categories' => count($this->posts->findPublishedCategories()),
            ],
            'search' => $search,
            'selectedStatus' => $status,
        ]);
    }

    #[Route('/nouveau', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm(new BlogPost(), $request, true);
    }

    #[Route('/{id}', name: '_edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function edit(int $id, Request $request): Response
    {
        return $this->handleForm($this->findPost($id), $request, false);
    }

    #[Route('/{id}/supprimer', name: '_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function delete(int $id, Request $request): Response
    {
        $post = $this->findPost($id);
        if (!$this->isCsrfTokenValid('delete_blog_post_'.$id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Le formulaire a expiré.');
        }

        $this->manager->delete($post);
        $this->addFlash('success', 'L’article a été supprimé.');

        return $this->redirectToRoute('admin_blog');
    }

    private function handleForm(BlogPost $post, Request $request, bool $isNew): Response
    {
        $choices = [];
        foreach ($post->getContentImages() as $index => $image) {
            $choices['Image '.($index + 1).' · '.basename($image['path'])] = $image['path'];
        }

        $form = $this->createForm(BlogPostType::class, $post, ['content_image_choices' => $choices]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $featuredImage = $form->get('featuredImageFile')->getData();
            $contentImages = $form->get('contentImageFiles')->getData();
            $this->manager->save(
                $post,
                $featuredImage instanceof UploadedFile ? $featuredImage : null,
                true === $form->get('featuredImageRemove')->getData(),
                is_array($contentImages) ? $contentImages : [],
                $form->get('removeContentImages')->getData() ?? [],
            );
            $this->addFlash('success', $isNew ? 'L’article a été créé.' : 'L’article a été mis à jour.');

            return $this->redirectToRoute('admin_blog_edit', ['id' => $post->getId()]);
        }

        return $this->render('admin/blog/form.html.twig', [
            'adminSection' => 'blog',
            'post' => $post,
            'form' => $form,
            'isNew' => $isNew,
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    private function findPost(int $id): BlogPost
    {
        $post = $this->posts->find($id);
        if (!$post instanceof BlogPost) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        return $post;
    }
}
