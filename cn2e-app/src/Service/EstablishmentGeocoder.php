<?php

namespace App\Service;

use App\Entity\Establishment;

class EstablishmentGeocoder
{
    public function __construct(
        private AddressGeocoder $geocoder
    ) {}

    public function hydrate(Establishment $establishment): void
    {
        $address = $establishment->getAddress();

        if (!$address) {
            return;
        }

        $hash = $this->makeHash($address);

        // skip si déjà traité
        if ($establishment->getAddressHash() === $hash) {
            return;
        }

        $geo = $this->geocoder->search($address);

        if (!$geo) {
            $establishment->setAddressHash($hash);
            return;
        }

        $props = $geo['properties'] ?? [];
        $coords = $geo['geometry']['coordinates'] ?? [];

        $establishment->setCity($props['city'] ?? null);

        $establishment->setDepartment($this->extractDepartmentCode($props));
        $establishment->setRegion($this->extractRegionName($props));

        $establishment->setLatitude($coords[1] ?? null);
        $establishment->setLongitude($coords[0] ?? null);

        $establishment->setAddress($props['label'] ?? $address);
        $establishment->setAddressHash($hash);
    }

    private function makeHash(string $address): string
    {
        return hash('sha256', mb_strtolower(trim($address)));
    }

    private function extractDepartmentCode(array $props): ?string
    {
        // BAN context = "numéro de département, Nom du département, Nom de la région"
        $context = $props['context'] ?? null;

        if (!$context) {
            return null;
        }

        $parts = explode(',', $context);

        return trim($parts[0] ?? '');
    }

    private function extractRegionName(array $props): ?string
    {
        $context = $props['context'] ?? null;

        if (!$context) {
            return null;
        }

        $parts = explode(',', $context);

        return trim($parts[2] ?? '');
    }
}