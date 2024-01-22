<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllCard'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getAllCard'])]
    private ?string $color = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getAllCard'])]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllCard'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Groups(['getAllCard'])]
    private ?bool $is_special = null;

    #[ORM\Column]
    #[Groups(['getAllCard'])]
    private ?bool $is_wild = null;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['updateCard'])]
    private ?DownloadedFiles $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isIsSpecial(): ?bool
    {
        return $this->is_special;
    }

    public function setIsSpecial(bool $is_special): static
    {
        $this->is_special = $is_special;

        return $this;
    }

    public function isIsWild(): ?bool
    {
        return $this->is_wild;
    }

    public function setIsWild(bool $is_wild): static
    {
        $this->is_wild = $is_wild;

        return $this;
    }

    public function getImage(): ?DownloadedFiles
    {
        return $this->image;
    }

    public function setImage(?DownloadedFiles $image): static
    {
        $this->image = $image;

        return $this;
    }
}
