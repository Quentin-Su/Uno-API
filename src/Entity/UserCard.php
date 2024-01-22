<?php

namespace App\Entity;

use App\Repository\UserCardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCardRepository::class)]
class UserCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userCards')]
    private ?UserGame $user_game_id = null;

    #[ORM\ManyToOne]
    private ?Card $card_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $obtained_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $used_at = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserGameId(): ?UserGame
    {
        return $this->user_game_id;
    }

    public function setUserGameId(?UserGame $user_game_id): static
    {
        $this->user_game_id = $user_game_id;

        return $this;
    }

    public function getCardId(): ?Card
    {
        return $this->card_id;
    }

    public function setCardId(?Card $card_id): static
    {
        $this->card_id = $card_id;

        return $this;
    }

    public function getObtainedAt(): ?\DateTimeImmutable
    {
        return $this->obtained_at;
    }

    public function setObtainedAt(\DateTimeImmutable $obtained_at): static
    {
        $this->obtained_at = $obtained_at;

        return $this;
    }

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->used_at;
    }

    public function setUsedAt(?\DateTimeImmutable $used_at): static
    {
        $this->used_at = $used_at;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
