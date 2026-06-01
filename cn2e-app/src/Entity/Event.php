<?php

namespace App\Entity;

use App\Entity\Traits\ImageUploadTrait;
use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use ImageUploadTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[Assert\NotBlank(message: 'event.title.required')]
    #[Assert\Length(max: 255, maxMessage: 'event.title.max')]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Assert\NotNull(message: 'event.start_date.required')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[Assert\GreaterThanOrEqual(
        propertyPath: 'startDate',
        message: 'event.end_date.after_start'
    )]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[Assert\NotBlank(message: 'event.location.required')]
    #[Assert\Length(max: 255, maxMessage: 'event.location.max')]
    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[Assert\NotBlank(message: 'event.short_description.required')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortDescription = null;

    #[Assert\NotBlank(message: 'event.content.required')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[Vich\UploadableField(
        mapping: 'event_image',
        fileNameProperty: 'imageName'
    )]
    private ?File $imageFile = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[Assert\NotNull(message: 'event.members_only.required')]
    #[ORM\Column]
    private ?bool $isMembersOnly = false;

    #[Assert\NotNull(message: 'event.registration.required')]
    #[ORM\Column]
    private ?bool $hasRegistration = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateSlug(): void
    {
        $this->slug = (new AsciiSlugger())
            ->slug($this->title)
            ->lower();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isMembersOnly(): ?bool
    {
        return $this->isMembersOnly;
    }

    public function setIsMembersOnly(bool $isMembersOnly): static
    {
        $this->isMembersOnly = $isMembersOnly;

        return $this;
    }

    public function hasRegistration(): ?bool
    {
        return $this->hasRegistration;
    }

    public function setHasRegistration(bool $hasRegistration): static
    {
        $this->hasRegistration = $hasRegistration;

        return $this;
    }
}
