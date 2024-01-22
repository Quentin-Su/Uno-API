<?php

namespace App\Entity;

use App\Repository\UserGameStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserGameStatsRepository::class)]
class UserGameStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userGameStats', cascade: ['persist', 'remove'])]
    private ?UserGame $user_game_id = null;

    #[ORM\Column]
    private ?int $user_rank = null;

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

    public function getUserRank(): ?int
    {
        return $this->user_rank;
    }

    public function setUserRank(int $user_rank): static
    {
        $this->user_rank = $user_rank;

        return $this;
    }
}
