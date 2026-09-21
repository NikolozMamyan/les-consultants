<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ThemePage;
use App\Repository\ThemePageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ThemePageManager
{
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly AdminDemoDataProvider $definitions,
        private readonly ThemePageRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads/theme')]
        private readonly string $uploadDirectory,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function pages(): array
    {
        return array_map($this->hydrateDefinition(...), $this->definitions->pages());
    }

    /** @return array<string, mixed>|null */
    public function page(string $slug): ?array
    {
        $definition = $this->definitions->page($slug);

        return null === $definition ? null : $this->hydrateDefinition($definition);
    }

    /**
     * @param array<string, mixed>                                                        $values
     * @param array<string, mixed>                                                        $carouselSettings
     * @param list<array{selector: string, file: UploadedFile}>                           $images
     */
    public function save(string $slug, array $values, array $carouselSettings, array $images): ThemePage
    {
        $definition = $this->definitions->page($slug);
        if (null === $definition) {
            throw new \InvalidArgumentException('Page inconnue.');
        }

        $allowedFields = $this->allowedFields($definition);
        $page = $this->repository->findBySlug($slug) ?? new ThemePage($slug);
        $content = $page->getContent();

        foreach ($values as $selector => $value) {
            if (!is_string($selector) || !isset($allowedFields[$selector]) || 'image' === $allowedFields[$selector]) {
                throw new \InvalidArgumentException('Un champ de contenu est invalide.');
            }
            if (!is_string($value)) {
                throw new \InvalidArgumentException('La valeur d’un champ est invalide.');
            }

            $value = trim($value);
            $maximumLength = 'textarea' === $allowedFields[$selector] ? 500 : ('button' === $allowedFields[$selector] ? 80 : 180);
            if ('' === $value || mb_strlen($value) > $maximumLength) {
                throw new \InvalidArgumentException(sprintf('Le champ « %s » doit contenir entre 1 et %d caractères.', $selector, $maximumLength));
            }

            $content[$selector] = $value;
        }

        foreach ($images as $image) {
            $selector = $image['selector'];
            $file = $image['file'];
            if (($allowedFields[$selector] ?? null) !== 'image') {
                throw new \InvalidArgumentException('Le champ image est invalide.');
            }

            $content[$selector] = $this->storeImage($slug, $file);
        }

        $allowedCarousels = $this->allowedCarousels($definition);
        $carousels = $page->getCarousels();
        foreach ($carouselSettings as $selector => $settings) {
            if (!is_string($selector) || !isset($allowedCarousels[$selector]) || !is_array($settings)) {
                throw new \InvalidArgumentException('Un réglage de carrousel est invalide.');
            }

            $interval = filter_var($settings['interval'] ?? null, FILTER_VALIDATE_INT);
            $mode = $settings['mode'] ?? null;
            if (false === $interval || $interval < 2000 || $interval > 10000 || !in_array($mode, ['cards', 'marquee'], true)) {
                throw new \InvalidArgumentException('Un réglage de carrousel est hors limites.');
            }

            $carousels[$selector] = ['interval' => $interval, 'mode' => $mode];
        }

        $page->setContent($content);
        $page->setCarousels($carousels);
        $page->touch();
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    /** @param array<string, mixed> $definition
     *  @return array<string, mixed>
     */
    private function hydrateDefinition(array $definition): array
    {
        $storedPage = $this->repository->findBySlug($definition['slug']);
        if (null === $storedPage) {
            return $definition;
        }

        $content = $storedPage->getContent();
        $carousels = $storedPage->getCarousels();
        foreach ($definition['sections'] as &$section) {
            foreach ($section['fields'] as &$field) {
                $field['value'] = $content[$field['selector']] ?? $field['value'];
            }
            unset($field);

            if (null === $section['carousel']) {
                continue;
            }

            $settings = $carousels[$section['selector']] ?? null;
            if (is_array($settings)) {
                $section['carousel']['interval'] = $settings['interval'];
                $section['carousel']['mode'] = $settings['mode'];
            }
            foreach ($section['carousel']['cards'] as &$card) {
                foreach ($card['fields'] as &$field) {
                    $field['value'] = $content[$field['selector']] ?? $field['value'];
                }
                unset($field);
            }
            unset($card);
        }
        unset($section);

        $definition['updated'] = $storedPage->getUpdatedAt()->format('d/m/Y à H:i');

        return $definition;
    }

    /** @param array<string, mixed> $definition
     *  @return array<string, string>
     */
    private function allowedFields(array $definition): array
    {
        $fields = [];
        foreach ($definition['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $fields[$field['selector']] = $field['type'];
            }
            foreach ($section['carousel']['cards'] ?? [] as $card) {
                foreach ($card['fields'] as $field) {
                    $fields[$field['selector']] = $field['type'];
                }
            }
        }

        return $fields;
    }

    /** @param array<string, mixed> $definition
     *  @return array<string, true>
     */
    private function allowedCarousels(array $definition): array
    {
        $carousels = [];
        foreach ($definition['sections'] as $section) {
            if (null !== $section['carousel']) {
                $carousels[$section['selector']] = true;
            }
        }

        return $carousels;
    }

    private function storeImage(string $slug, UploadedFile $file): string
    {
        if (!$file->isValid() || $file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('L’image doit être valide et ne pas dépasser 5 Mo.');
        }

        $extension = self::ALLOWED_IMAGE_TYPES[$file->getMimeType() ?? ''] ?? null;
        if (null === $extension) {
            throw new \InvalidArgumentException('Seules les images JPG, PNG et WebP sont acceptées.');
        }

        $directory = $this->uploadDirectory.'/'.$slug;
        $filename = bin2hex(random_bytes(16)).'.'.$extension;
        $file->move($directory, $filename);

        return '/uploads/theme/'.$slug.'/'.$filename;
    }
}
