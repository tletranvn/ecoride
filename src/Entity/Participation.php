<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    private ?Trajet $trajet = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'integer')]
    private ?int $creditsUtilises = null;

    //US11 passager peut valider le trajet quand il est terminé
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $trajetValide = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTrajet(): ?Trajet
    {
        return $this->trajet;
    }

    public function setTrajet(?Trajet $trajet): static
    {
        $this->trajet = $trajet;

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

    public function getCreditsUtilises(): ?int
    {
        return $this->creditsUtilises;
    }

    public function setCreditsUtilises(int $credits): static
    {
        $this->creditsUtilises = $credits;

        return $this;
    }

    // US11 passager peut valider le trajet quand il est terminé
    public function isTrajetValide(): ?bool
    {
        return $this->trajetValide;
    }

    public function setTrajetValide(?bool $valide): static
    {
        $this->trajetValide = $valide;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

}
