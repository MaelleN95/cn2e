<?php

namespace App\Entity;

use App\Repository\EstablishmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: EstablishmentRepository::class)]
class Establishment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[Assert\NotBlank(message: 'establishment.name.required')]
    #[Assert\Length(max: 255, maxMessage: 'establishment.name.max')]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // #[Assert\NotBlank(message: 'establishment.city.required')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    // #[Assert\NotBlank(message: 'establishment.department.required')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $department = null;

    // #[Assert\NotBlank(message: 'establishment.region.required')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $region = null;

    #[Assert\NotBlank(message: 'establishment.address.required')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $address = null;

    #[ORM\Column(length: 64)]
    private ?string $addressHash = 'null';

    #[ORM\Column]
    private ?float $latitude = 1;

    #[ORM\Column]
    private ?float $longitude = 1;

    #[Assert\Regex(
        pattern: '/^\+?[0-9\s\-]{6,20}$/',
        message: 'establishment.phone.invalid'
    )]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[Assert\Email(message: 'establishment.email.invalid')]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[Assert\Url(message: 'establishment.website.invalid')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'establishment')]
    private Collection $users;

    /**
     * @var Collection<int, AcademicProgram>
     */
    #[ORM\ManyToMany(targetEntity: AcademicProgram::class, mappedBy: 'establishments')]
    private Collection $academicPrograms;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->academicPrograms = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? 'ID : ' . $this->id;
    }

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
            ->slug($this->name)
            ->lower();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressHash(): ?string
    {
        return $this->addressHash;
    }

    public function setAddressHash(string $addressHash): static
    {
        $this->addressHash = $addressHash;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }


    /**
     * @return Collection<int, AcademicProgram>
     */
    public function getAcademicPrograms(): Collection
    {
        return $this->academicPrograms;
    }

    public function addAcademicProgram(AcademicProgram $academicProgram): static
    {
        if (!$this->academicPrograms->contains($academicProgram)) {
            $this->academicPrograms->add($academicProgram);
            $academicProgram->addEstablishment($this);
        }

        return $this;
    }

    public function removeAcademicProgram(AcademicProgram $academicProgram): static
    {
        if ($this->academicPrograms->removeElement($academicProgram)) {
            $academicProgram->removeEstablishment($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setEstablishment($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getEstablishment() === $this) {
                $user->setEstablishment(null);
            }
        }

        return $this;
    }
}
