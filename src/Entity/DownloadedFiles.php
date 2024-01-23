<?php

namespace App\Entity;

use DateTimeImmutable;
use App\Repository\DownloadedFilesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: DownloadedFilesRepository::class)]
class DownloadedFiles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $real_name = null;

    #[ORM\Column(length: 255)]
    private ?string $real_path = null;

    #[ORM\Column(length: 255)]
    private ?string $public_path = null;

    #[ORM\Column(length: 255)]
    private ?string $mine_type = null;

    private ?File $file = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $update_at = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'image', targetEntity: Card::class)]
    private Collection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRealName(): ?string
    {
        return $this->real_name;
    }

    public function setRealName(string $real_name): static
    {
        $this->real_name = $real_name;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->real_path;
    }

    public function setRealPath(string $real_path): static
    {
        $this->real_path = $real_path;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->public_path;
    }

    public function setPublicPath(string $public_path): static
    {
        $this->public_path = $public_path;

        return $this;
    }

    public function getMineType(): ?string
    {
        return $this->mine_type;
    }

    public function setMineType(string $mine_type): static
    {
        $this->mine_type = $mine_type;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $slugger = new AsciiSlugger();
        $parseslug = $slugger->slug($slug . time());
        $this->slug = $parseslug . '.' . $this->getFile()->getClientOriginalExtension();

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): static
    {
        $this->file = $file;
        
        $this->setName($file->getClientOriginalName());
        $this->setRealName($file->getClientOriginalName());
        $this->setMineType($file->getClientMimeType());

        $this->setPublicPath('./documents/pictures');
        $this->setRealPath('documents/pictures');
        $this->setSlug($this->getRealName());

        $this->setCreatedAt(new DateTimeImmutable());
        $this->setStatus('on');
        
        $file->move(
            $this->getPublicPath(),
            $this->getSlug()
        );

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setImage($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getImage() === $this) {
                $card->setImage(null);
            }
        }

        return $this;
    }
}
