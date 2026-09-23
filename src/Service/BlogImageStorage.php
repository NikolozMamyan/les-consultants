<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class BlogImageStorage
{
    private const PUBLIC_PREFIX = 'uploads/blog/';

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private string $publicDirectory,
    ) {
    }

    public function store(UploadedFile $file, string $prefix): string
    {
        $contents = file_get_contents($file->getPathname());
        $source = false === $contents ? false : imagecreatefromstring($contents);
        if (false === $source) {
            throw new \RuntimeException('L’image envoyée ne peut pas être traitée.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, 2000 / $sourceWidth, 1400 / $sourceHeight);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        $directory = $this->publicDirectory.'/'.self::PUBLIC_PREFIX;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            imagedestroy($source);
            imagedestroy($target);
            throw new \RuntimeException('Le dossier des images du blog ne peut pas être créé.');
        }

        $filename = trim($prefix, '-').'-'.bin2hex(random_bytes(6)).'.webp';
        $destination = $directory.$filename;
        $saved = imagewebp($target, $destination, 86);
        imagedestroy($source);
        imagedestroy($target);

        if (!$saved) {
            throw new \RuntimeException('L’image du blog ne peut pas être enregistrée.');
        }

        return self::PUBLIC_PREFIX.$filename;
    }

    public function delete(?string $path): void
    {
        if (null === $path || !str_starts_with($path, self::PUBLIC_PREFIX)) {
            return;
        }

        $file = $this->publicDirectory.'/'.self::PUBLIC_PREFIX.basename($path);
        if (is_file($file)) {
            unlink($file);
        }
    }
}
