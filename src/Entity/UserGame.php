<?php

namespace App\Entity;

use App\Repository\UserGameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserGameRepository::class)]
class UserGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userGames')]
    private ?User $user_id = null;

    #[ORM\ManyToOne(inversedBy: 'userGames')]
    private ?Game $game_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $joined_at = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\OneToOne(mappedBy: 'user_game_id', cascade: ['persist', 'remove'])]
    private ?UserGameStats $userGameStats = null;

    #[ORM\OneToMany(mappedBy: 'user_game_id', targetEntity: UserCard::class)]
    private Collection $userCards;

    public function __construct()
    {
        $this->userCards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

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

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joined_at;
    }

    public function setJoinedAt(\DateTimeImmutable $joined_at): static
    {
        $this->joined_at = $joined_at;

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

    public function getUserGameStats(): ?UserGameStats
    {
        return $this->userGameStats;
    }

    public function setUserGameStats(?UserGameStats $userGameStats): static
    {
        // unset the owning side of the relation if necessary
        if ($userGameStats === null && $this->userGameStats !== null) {
            $this->userGameStats->setUserGameId(null);
        }

        // set the owning side of the relation if necessary
        if ($userGameStats !== null && $userGameStats->getUserGameId() !== $this) {
            $userGameStats->setUserGameId($this);
        }

        $this->userGameStats = $userGameStats;

        return $this;
    }

    /**
     * @return Collection<int, UserCard>
     */
    public function getUserCards(): Collection
    {
        return $this->userCards;
    }

    public function addUserCard(UserCard $userCard): static
    {
        if (!$this->userCards->contains($userCard)) {
            $this->userCards->add($userCard);
            $userCard->setUserGameId($this);
        }

        return $this;
    }

    public function removeUserCard(UserCard $userCard): static
    {
        if ($this->userCards->removeElement($userCard)) {
            // set the owning side to null (unless already changed)
            if ($userCard->getUserGameId() === $this) {
                $userCard->setUserGameId(null);
            }
        }

        return $this;
    }
}
