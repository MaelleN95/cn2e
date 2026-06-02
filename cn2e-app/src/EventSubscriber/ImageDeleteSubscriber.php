<?php

namespace App\EventSubscriber;

use App\Service\Image\ImageCleanupService;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImageDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ImageCleanupService $cleanup
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::PRE_REMOVE => 'onDelete',
        ];
    }

    public function onDelete(Event $event): void
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();

        $fileNameProperty = $mapping->getFileNamePropertyName();
        $getter = 'get' . ucfirst($fileNameProperty);

        if (!method_exists($object, $getter)) {
            return;
        }

        $filename = $object->$getter();
        if (!$filename) {
            return;
        }

        $uploadDir = $mapping->getUploadDestination();
        $path = $uploadDir . '/' . $filename;

        $this->cleanup->cleanup($path);
    }
}