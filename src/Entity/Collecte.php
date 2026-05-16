<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CollecteRepository;

#[ORM\Entity(repositoryClass: CollecteRepository::class)]
class Collecte
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\Column(length: 255)] private ?string $reference = null;

    #[ORM\Column(length: 255)] private ?string $camion = null;

    #[ORM\Column(length: 50)] private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]

    #[ORM\JoinColumn(nullable: false)]

    private ?Utilisateur $collecteur = null;

    #[ORM\Column] private ?\DateTimeImmutable $dateCollecte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getCamion(): ?string
    {
        return $this->camion;
    }

    public function setCamion(string $camion): static
    {
        $this->camion = $camion;
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

    public function getCollecteur(): ?Utilisateur
    {
        return $this->collecteur;
    }

    public function setCollecteur(?Utilisateur $collecteur): static
    {
        $this->collecteur = $collecteur;
        return $this;
    }

    public function getDateCollecte(): ?\DateTimeImmutable
    {
        return $this->dateCollecte;
    }

    public function setDateCollecte(\DateTimeImmutable $dateCollecte): static
    {
        $this->dateCollecte = $dateCollecte;
        return $this;
    }

}
