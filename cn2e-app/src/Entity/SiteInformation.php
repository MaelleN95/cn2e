<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\SiteInformationRepository::class)]
class SiteInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private string $organizationName = 'Comité National des EREA/LEA et ERPD';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $acronym = 'CN2E';

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private string $postalAddressLine1 = '29 Rue de Cronstadt';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $postalAddressLine2 = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 20)]
    private string $postalCode = '75015';

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private string $city = 'Paris';

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private string $country = 'France';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(length: 180)]
    private string $contactEmail = 'secretariat.cn2e@gmail.com';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(length: 180)]
    private string $senderEmail = 'contact@koji-dev.fr';

    #[Assert\Url]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinUrl = null;

    #[Assert\Url]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagramUrl = null;

    #[Assert\Url]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookUrl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizationName(): string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(string $organizationName): static
    {
        $this->organizationName = $organizationName;

        return $this;
    }

    public function getAcronym(): ?string
    {
        return $this->acronym;
    }

    public function getShortName(): string
    {
        return $this->acronym ?: $this->organizationName;
    }

    public function setAcronym(?string $acronym): static
    {
        $this->acronym = $acronym;

        return $this;
    }

    public function getPostalAddressLine1(): string
    {
        return $this->postalAddressLine1;
    }

    public function setPostalAddressLine1(string $postalAddressLine1): static
    {
        $this->postalAddressLine1 = $postalAddressLine1;

        return $this;
    }

    public function getPostalAddressLine2(): ?string
    {
        return $this->postalAddressLine2;
    }

    public function setPostalAddressLine2(?string $postalAddressLine2): static
    {
        $this->postalAddressLine2 = $postalAddressLine2;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): static
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function setLinkedinUrl(?string $linkedinUrl): static
    {
        $this->linkedinUrl = $linkedinUrl;

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): static
    {
        $this->instagramUrl = $instagramUrl;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): static
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    public function getDisplayName(): string
    {
        if (!$this->acronym) {
            return $this->organizationName;
        }

        return sprintf('%s (%s)', $this->organizationName, $this->acronym);
    }
}