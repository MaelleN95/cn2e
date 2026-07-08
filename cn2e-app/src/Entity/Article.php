<?php

namespace App\Entity;

use App\Entity\Traits\ImageUploadTrait;
use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    use ImageUploadTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[Assert\NotBlank(message: 'article.title.required')]
    #[Assert\Length(max: 255, maxMessage: 'article.title.max')]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Assert\NotNull(message: 'article.published_at.required')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $publishedAt;

    #[Assert\NotBlank(message: 'article.short_description.required')]
    #[Assert\Length(max: 1000, maxMessage: 'article.short_description.max')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortDescription = null;

    #[Assert\NotBlank(message: 'article.content.required')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[Vich\UploadableField(
        mapping: 'article_image',
        fileNameProperty: 'imageName'
    )]
    private ?File $imageFile = null;

    #[Assert\Valid]
    #[Assert\Count(max: 5, maxMessage: 'article.documents.max')]
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Document::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $documents;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[Assert\NotNull(message: 'article.members_only.required')]
    #[ORM\Column]
    private ?bool $isMembersOnly = false;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[Assert\Url(message: 'article.video_url.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'article.video_url.max')] 
    #[ORM\Column(type: Types::TEXT)]
    private ?string $videoUrl = null;

    public function __construct()
    {
        $this->publishedAt = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
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

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

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

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setArticle($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getArticle() === $this) {
                $document->setArticle(null);
            }
        }

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }
}
