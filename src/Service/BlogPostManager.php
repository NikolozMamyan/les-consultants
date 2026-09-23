<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class BlogPostManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BlogPostRepository $posts,
        private BlogImageStorage $images,
        private SluggerInterface $slugger,
    ) {
    }

    /**
     * @param list<UploadedFile> $contentImageFiles
     * @param list<string>       $removedContentImages
     */
    public function save(
        BlogPost $post,
        ?UploadedFile $featuredImageFile,
        bool $removeFeaturedImage,
        array $contentImageFiles,
        array $removedContentImages,
    ): void {
        $post->setSlug($this->uniqueSlug($post));
        if ('' === $post->getMetaTitle()) {
            $post->setMetaTitle(mb_substr($post->getTitle(), 0, 255));
        }
        if ('' === $post->getMetaDescription()) {
            $post->setMetaDescription(mb_substr(strip_tags($post->getExcerpt()), 0, 255));
        }

        if ($featuredImageFile instanceof UploadedFile) {
            $this->images->delete($post->getFeaturedImage());
            $post->setFeaturedImage($this->images->store($featuredImageFile, $post->getSlug().'-featured'));
        } elseif ($removeFeaturedImage) {
            $this->images->delete($post->getFeaturedImage());
            $post->setFeaturedImage(null);
        }

        $contentImages = [];
        foreach ($post->getContentImages() as $image) {
            if (in_array($image['path'], $removedContentImages, true)) {
                $this->images->delete($image['path']);
                continue;
            }
            $contentImages[] = $image;
        }

        foreach ($contentImageFiles as $index => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $contentImages[] = [
                'path' => $this->images->store($file, $post->getSlug().'-'.($index + 1)),
                'alt' => $post->getTitle(),
            ];
        }
        $post->setContentImages($contentImages);
        $post->touch();

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    public function delete(BlogPost $post): void
    {
        $this->images->delete($post->getFeaturedImage());
        foreach ($post->getContentImages() as $image) {
            $this->images->delete($image['path']);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    private function uniqueSlug(BlogPost $post): string
    {
        $requested = '' !== $post->getSlug() ? $post->getSlug() : $post->getTitle();
        $base = strtolower((string) $this->slugger->slug($requested));
        $base = '' !== $base ? $base : 'article';
        $candidate = $base;
        $suffix = 2;

        while ($this->posts->slugExistsForAnotherPost($candidate, $post->getId())) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
