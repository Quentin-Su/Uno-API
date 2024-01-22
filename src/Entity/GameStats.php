<?php

namespace App\Entity;

use App\Repository\GameStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameStatsRepository::class)]
class GameStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $player_count = null;

    #[ORM\Column]
    private ?int $total_card = null;

    #[ORM\Column]
    private ?int $total_malus = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $started_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $end_at = null;

    #[ORM\OneToOne(inversedBy: 'gameStats', cascade: ['persist', 'remove'])]
    private ?Game $game_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerCount(): ?int
    {
        return $this->player_count;
    }

    public function setPlayerCount(int $player_count): static
    {
        $this->player_count = $player_count;

        return $this;
    }

    public function getTotalCard(): ?int
    {
        return $this->total_card;
    }

    public function setTotalCard(int $total_card): static
    {
        $this->total_card = $total_card;

        return $this;
    }

    public function getTotalMalus(): ?int
    {
        return $this->total_malus;
    }

    public function setTotalMalus(int $total_malus): static
    {
        $this->total_malus = $total_malus;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->started_at;
    }

    public function setStartedAt(\DateTimeImmutable $started_at): static
    {
        $this->started_at = $started_at;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->end_at;
    }

    public function setEndAt(?\DateTimeImmutable $end_at): static
    {
        $this->end_at = $end_at;

        return $this;
    }

    public function getGameId(): ?Game
    {
        return $this->game_id;
    }

    public function setGameId(?Game $game_id): static
    {
        $this->game_id = $game_id;

        return $this;
    }
}
