<?php

namespace App\EventSubscriber;

use App\Service\Image\WebpConverter;
use App\Service\Image\ImageVariantGenerator;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImageUploadSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WebpConverter $converter,
        private ImageVariantGenerator $generator
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD => 'onUpload',
        ];
    }

    private function getSizes(string $mappingName): array
    {
        return match ($mappingName) {
            'user_image' => [
                'thumbnail' => 100,
            ],

            default => [
                'small' => 300,
                'medium' => 550,
                'large' => 800,
            ],
        };
    }

    public function onUpload(Event $event): void
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();

        $fileNameProperty = $mapping->getFileNamePropertyName();
        $getter = 'get' . ucfirst($fileNameProperty);
        $setter = 'set' . ucfirst($fileNameProperty);

        if (!method_exists($object, $getter)) {
            return;
        }

        $filename = $object->$getter();
        if (!$filename) {
            return;
        }

        $uploadDir = $mapping->getUploadDestination();
        $originalPath = $uploadDir . '/' . $filename;

        if (!file_exists($originalPath)) {
            return;
        }

        // 1. conversion WebP
        $webpPath = $this->converter->convert($originalPath);

        // suppression original
        if (
            $originalPath !== $webpPath &&
            file_exists($originalPath)
        ) {
            unlink($originalPath);
        }

        $newFilename = basename($webpPath);

        if (method_exists($object, $setter)) {
            $object->$setter($newFilename);
        }

        // 2. génération des images de différentes tailles
        $sizes = $this->getSizes($mapping->getMappingName());
        $this->generator->generate($webpPath, $sizes);
    }
}