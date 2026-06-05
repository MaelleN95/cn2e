<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(length: 255)]
    private ?string $pdfName = null;

    #[Assert\File(
        mimeTypes: ['application/pdf', 'application/x-pdf'],
        mimeTypesMessage: 'document.pdf.invalid'
    )]
    #[Vich\UploadableField(mapping: 'article_pdf', fileNameProperty: 'pdfName')]
    private ?File $pdfFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }

    public function getPdfName(): ?string
    {
        return $this->pdfName;
    }

    public function setPdfName(?string $pdfName): void
    {
        $this->pdfName = $pdfName;
    }

    public function getPdfFile(): ?File
    {
        return $this->pdfFile;
    }

    public function setPdfFile(?File $file = null): void
    {
        $this->pdfFile = $file;

        if ($file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}