<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PoubelleRepository;

#[ORM\Entity(repositoryClass: PoubelleRepository::class)]
class Poubelle
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\Column(length: 255)] private ?string $reference = null;

    #[ORM\Column] private ?float $latitude = null;

    #[ORM\Column] private ?float $longitude = null;

    #[ORM\ManyToOne(targetEntity: CategoriePoubelle::class)]

    #[ORM\JoinColumn(nullable: false)]

    private ?CategoriePoubelle $categorie = null;

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

    public function getCategorie(): ?CategoriePoubelle
    {
        return $this->categorie;
    }

    public function setCategorie(?CategoriePoubelle $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

}
