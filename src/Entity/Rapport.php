<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RapportRepository;

#[ORM\Entity(repositoryClass: RapportRepository::class)]
class Rapport
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\Column(type: "text")] private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)] private ?string $photo = null;

    #[ORM\OneToOne(targetEntity: Collecte::class, cascade: ["persist", "remove"])]

    #[ORM\JoinColumn(nullable: false)]

    private ?Collecte $collecte = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCollecte(): ?Collecte
    {
        return $this->collecte;
    }

    public function setCollecte(Collecte $collecte): static
    {
        $this->collecte = $collecte;
        return $this;
    }

}
