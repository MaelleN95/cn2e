<?php

namespace App\Service\Image;

use Imagick;

class ImageVariantGenerator
{
    private array $sizes = [
        'thumbnail' => 100,
        'small' => 300,
        'medium' => 550,
        'large' => 800,
    ];

    public function generate(string $webpPath): void
    {
        $source = new Imagick($webpPath);

        $originalWidth = $source->getImageWidth();
        $originalHeight = $source->getImageHeight();

        foreach ($this->sizes as $suffix => $width) {
            $clone = clone $source;

            $ratio = $originalHeight / $originalWidth;
            $height = (int) round($width * $ratio);

            $clone->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);

            $newPath = $this->buildPath($webpPath, $suffix);

            $clone->writeImage($newPath);

            $clone->clear();
            $clone->destroy();
        }

        $source->clear();
        $source->destroy();
    }

    private function buildPath(string $webpPath, string $suffix): string
    {
        return pathinfo($webpPath, PATHINFO_DIRNAME) . '/' .
               pathinfo($webpPath, PATHINFO_FILENAME) .
               "_{$suffix}.webp";
    }
}