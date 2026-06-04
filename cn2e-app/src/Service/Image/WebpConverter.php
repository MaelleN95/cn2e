<?php 

namespace App\Service\Image;

use Imagick;

class WebpConverter
{
    public function convert(string $path, int $quality = 70): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'webp') {
            return $path;
        }

        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $webpPath = $dir . '/' . $filename . '.webp';

        $imagick = new Imagick($path);
        $imagick->autoOrient();
        $imagick->stripImage();
        $imagick->setImageFormat('webp');
        $imagick->setImageCompressionQuality($quality);

        $success = $imagick->writeImage($webpPath);

        $imagick->clear();
        $imagick->destroy();

        if (!$success || !file_exists($webpPath)) {
            throw new \RuntimeException('WebP conversion failed: ' . $webpPath);
        }

        return $webpPath;
    }
}