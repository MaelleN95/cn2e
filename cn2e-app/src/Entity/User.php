<?php

namespace App\Entity;

use App\Entity\Traits\ImageUploadTrait;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use App\Validator\HasCn2eRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[HasCn2eRole]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'user.email.unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use ImageUploadTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'user.email.required')]
    #[Assert\Email(message: 'user.email.invalid')]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    private const ROLE_PRIORITY = [
        'ROLE_SUPER_ADMIN' => 4,
        'ROLE_CN2E_ADMIN' => 3,
        'ROLE_LOCAL_ADMIN' => 2,
        'ROLE_CN2E_MEMBER' => 1,
        'ROLE_USER' => 0,
    ];

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(message: 'user.lastname.required')]
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[Assert\NotBlank(message: 'user.firstname.required')]
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profession = null;

    #[Vich\UploadableField(
        mapping: 'user_image',
        fileNameProperty: 'imageName'
    )]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cn2eRole = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[Assert\NotNull(message: 'user.status.required')]
    #[ORM\Column(enumType: UserStatus::class)]
    private UserStatus $status = UserStatus::PENDING;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isVerified = false;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $articles;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Establishment $establishment = null;

    // Pour le message optionnel lors du registerForm
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requestMessage = null;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function __toString(): string
    {
        return $this->getFullName() !== ''
            ? $this->getFullName()
            : $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPrimaryRole(): string
    {
        $roles = $this->getRoles();

        $highestRole = 'ROLE_USER';
        $highestPriority = 0;

        foreach ($roles as $role) {
            $priority = self::ROLE_PRIORITY[$role] ?? 0;

            if ($priority > $highestPriority) {
                $highestPriority = $priority;
                $highestRole = $role;
            }
        }

        return $highestRole;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * ATTENTION : l'utilisateur possède un File (photo de profil) qui n'est pas sérialisable, il est donc exclu de la sérialisation. Seules les propriétés nécessaires à l'identification et à la gestion de l'utilisateur sont sérialisées.
     * Il est important de ne pas sérialiser le mot de passe en clair, même si c'est un hash, pour éviter les risques de sécurité en cas de fuite de données.
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => hash('crc32c', $this->password),
            'roles' => $this->roles,
            'lastName' => $this->lastName,
            'firstName' => $this->firstName,
            'profession' => $this->profession,
            'cn2eRole' => $this->cn2eRole,
            'status' => $this->status->value,
            'isVerified' => $this->isVerified,
            'imageName' => $this->imageName,
            'lastLoginAt' => $this->lastLoginAt?->format(\DateTimeInterface::ATOM),
            'requestMessage' => $this->requestMessage,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->roles = $data['roles'] ?? [];
        $this->lastName = $data['lastName'] ?? null;
        $this->firstName = $data['firstName'] ?? null;
        $this->profession = $data['profession'] ?? null;
        $this->cn2eRole = $data['cn2eRole'] ?? null;
        $this->status = isset($data['status']) ? UserStatus::from($data['status']) : UserStatus::PENDING;
        $this->isVerified = $data['isVerified'] ?? false;
        $this->imageName = $data['imageName'] ?? null;
        $this->lastLoginAt = isset($data['lastLoginAt']) ? new \DateTimeImmutable($data['lastLoginAt']) : null;
        $this->requestMessage = $data['requestMessage'] ?? null;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getCn2eRole(): ?string
    {
        return $this->cn2eRole;
    }

    public function setCn2eRole(?string $cn2eRole): static
    {
        $this->cn2eRole = $cn2eRole;

        return $this;
    }


    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): static
    {
        $this->establishment = $establishment;

        return $this;
    }

    public function getRequestMessage(): ?string
    {
        return $this->requestMessage;
    }

    public function setRequestMessage(?string $requestMessage): static
    {
        $this->requestMessage = $requestMessage;

        return $this;
    }
}
