<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['createGame', 'joinGame', 'getUserStuff'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['createGame'])]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[Groups(['createGame', 'joinGame', 'getUserStuff'])]
    private ?User $creator_id = null;

    #[ORM\Column]
    #[Groups(['createGame'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $update_at = null;

    #[ORM\OneToMany(mappedBy: 'game_id', targetEntity: UserGame::class)]
    private Collection $userGames;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne]
    private ?Card $lastCard = null;

    #[ORM\ManyToOne]
    private ?User $lastUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $selectedColor = null;

    #[ORM\Column(nullable: true)]
    private ?bool $reverse = null;

    #[ORM\Column(nullable: true)]
    private ?bool $specialPlayed = null;

    public function __construct()
    {
        $this->userGames = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCreatorId(): ?User
    {
        return $this->creator_id;
    }

    public function setCreatorId(?User $creator_id): static
    {
        $this->creator_id = $creator_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->update_at;
    }

    public function setUpdateAt(?\DateTimeImmutable $update_at): static
    {
        $this->update_at = $update_at;

        return $this;
    }

    /**
     * @return Collection<int, UserGame>
     */
    public function getUserGames(): Collection
    {
        return $this->userGames;
    }

    public function addUserGame(UserGame $userGame): static
    {
        if (!$this->userGames->contains($userGame)) {
            $this->userGames->add($userGame);
            $userGame->setGameId($this);
        }

        return $this;
    }

    public function removeUserGame(UserGame $userGame): static
    {
        if ($this->userGames->removeElement($userGame)) {
            // set the owning side to null (unless already changed)
            if ($userGame->getGameId() === $this) {
                $userGame->setGameId(null);
            }
        }

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

    public function getLastCard(): ?Card
    {
        return $this->lastCard;
    }

    public function setLastCard(?Card $lastCard): static
    {
        $this->lastCard = $lastCard;

        return $this;
    }

    public function getLastUser(): ?User
    {
        return $this->lastUser;
    }

    public function setLastUser(?User $lastUser): static
    {
        $this->lastUser = $lastUser;

        return $this;
    }

    public function getSelectedColor(): ?string
    {
        return $this->selectedColor;
    }

    public function setSelectedColor(?string $selectedColor): static
    {
        $this->selectedColor = $selectedColor;

        return $this;
    }

    public function isReverse(): ?bool
    {
        return $this->reverse;
    }

    public function setReverse(?bool $reverse): static
    {
        $this->reverse = $reverse;

        return $this;
    }

    public function isSpecialPlayed(): ?bool
    {
        return $this->specialPlayed;
    }

    public function setSpecialPlayed(?bool $specialPlayed): static
    {
        $this->specialPlayed = $specialPlayed;

        return $this;
    }
}
