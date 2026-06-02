<?php

namespace App\Service\Image;

class ImageCleanupService
{
    private array $suffixes = ['small', 'medium', 'large'];

    public function cleanup(string $path): void
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $main = $dir . '/' . $filename . '.webp';

        if (file_exists($main)) {
            unlink($main);
        }

        foreach ($this->suffixes as $suffix) {
            $variant = $dir . '/' . $filename . "_{$suffix}.webp";

            if (file_exists($variant)) {
                unlink($variant);
            }
        }
    }
}