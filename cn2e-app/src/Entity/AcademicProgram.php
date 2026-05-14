<?php

namespace App\Entity;

use App\Repository\AcademicProgramRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AcademicProgramRepository::class)]
class AcademicProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $level = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @var Collection<int, Establishment>
     */
    #[ORM\ManyToMany(targetEntity: Establishment::class, inversedBy: 'academicPrograms')]
    private Collection $establishments;

    public function __construct()
    {
        $this->establishments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? 'ID : ' . $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
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

    /**
     * @return Collection<int, Establishment>
     */
    public function getEstablishments(): Collection
    {
        return $this->establishments;
    }

    public function addEstablishment(Establishment $establishment): static
    {
        if (!$this->establishments->contains($establishment)) {
            $this->establishments->add($establishment);
        }

        return $this;
    }

    public function removeEstablishment(Establishment $establishment): static
    {
        $this->establishments->removeElement($establishment);

        return $this;
    }
}
