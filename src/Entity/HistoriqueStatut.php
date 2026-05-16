<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\HistoriqueStatutRepository;

#[ORM\Entity(repositoryClass: HistoriqueStatutRepository::class)]
class HistoriqueStatut
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column] private ?int $id = null;

    #[ORM\Column(length: 255)] private ?string $entiteConcernee = null;

    #[ORM\Column] private ?int $entiteId = null;

    #[ORM\Column(length: 50)] private ?string $nouveauStatut = null;

    #[ORM\Column] private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntiteConcernee(): ?string
    {
        return $this->entiteConcernee;
    }

    public function setEntiteConcernee(string $entiteConcernee): static
    {
        $this->entiteConcernee = $entiteConcernee;
        return $this;
    }

    public function getEntiteId(): ?int
    {
        return $this->entiteId;
    }

    public function setEntiteId(int $entiteId): static
    {
        $this->entiteId = $entiteId;
        return $this;
    }

    public function getNouveauStatut(): ?string
    {
        return $this->nouveauStatut;
    }

    public function setNouveauStatut(string $nouveauStatut): static
    {
        $this->nouveauStatut = $nouveauStatut;
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
