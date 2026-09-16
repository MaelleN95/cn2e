<?php

namespace App\Entity;

interface SluggableInterface
{
    public function getId(): ?int;

    public function getSlug(): ?string;

    public function setSlug(string $slug): static;

    /**
     * Indique quel champ sert de base au slug (ex : title, name).
     */
    public function getSlugSource(): ?string;
}
