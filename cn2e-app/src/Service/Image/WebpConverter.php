<?php 

namespace App\Service\Image;

use Imagick;

class WebpConverter
{
    public function convert(string $path, int $quality = 70): string
    {
        $imagick = new Imagick($path);
        $imagick->autoOrient();
        $imagick->stripImage();
        $imagick->setImageFormat('webp');
        $imagick->setImageCompressionQuality($quality);

        $newPath = pathinfo($path, PATHINFO_DIRNAME) . '/' .
                   pathinfo($path, PATHINFO_FILENAME) . '.webp';

        $imagick->writeImage($newPath);

        $imagick->clear();
        $imagick->destroy();

        return $newPath;
    }
}