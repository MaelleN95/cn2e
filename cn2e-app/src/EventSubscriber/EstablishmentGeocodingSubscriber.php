<?php

namespace App\EventSubscriber;

use App\Entity\Establishment;
use App\Service\AddressGeocoder;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class EstablishmentGeocodingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AddressGeocoder $geocoder
    ) {
        dd('test');
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->handle($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->handle($args->getObject());
    }

    private function handle(object $entity): void
    {
        if (!$entity instanceof Establishment) {
            return;
        }

        $address = $entity->getAddress();

        if (!$address) {
            return;
        }

        $geo = $this->geocoder->search($address);

        if (!$geo) {
            return;
        }

        $props = $geo['properties'];
        $coords = $geo['geometry']['coordinates'];

        $entity->setAddress($props['label'] ?? $address);
        $entity->setCity($props['city'] ?? null);
        $entity->setDepartment($props['context'] ?? null);
        $entity->setLatitude($coords[1] ?? null);
        $entity->setLongitude($coords[0] ?? null);
    }
}