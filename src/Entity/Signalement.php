<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SignalementRepository;

#[ORM\Entity(repositoryClass: SignalementRepository::class)]
class Signalement
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\Column(length: 255)] private ?string $titre = null;

    #[ORM\Column(type: "text")] private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)] private ?string $photo = null;

    #[ORM\Column(length: 50)] private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]

    #[ORM\JoinColumn(nullable: false)]

    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Poubelle::class)]

    private ?Poubelle $poubelle = null;

    #[ORM\ManyToOne(targetEntity: CategorieSignalement::class)]

    #[ORM\JoinColumn(nullable: false)]

    private ?CategorieSignalement $categorie = null;

    #[ORM\Column] private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getPoubelle(): ?Poubelle
    {
        return $this->poubelle;
    }

    public function setPoubelle(?Poubelle $poubelle): static
    {
        $this->poubelle = $poubelle;
        return $this;
    }

    public function getCategorie(): ?CategorieSignalement
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieSignalement $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

}
