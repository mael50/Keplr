<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GithubRepository::class)]
class GithubRepository
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $lastRelease = null;

    #[ORM\Column(length: 255)]
    private ?string $releaseFeedUrl = null;

    /**
     * @var Collection<int, Release>
     */
    #[ORM\OneToMany(
        targetEntity: Release::class,
        mappedBy: 'githubRepo',
        cascade: ['persist']
    )]
    private Collection $releases;

    #[ORM\ManyToOne(inversedBy: 'githubRepositories')]
    private ?User $user = null;

    public function __construct()
    {
        $this->releases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): static
    {
        $this->owner = $owner;

        return $this;
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

    public function getLastRelease(): ?string
    {
        return $this->lastRelease;
    }

    public function setLastRelease(string $lastRelease): static
    {
        $this->lastRelease = $lastRelease;

        return $this;
    }

    public function getReleaseFeedUrl(): ?string
    {
        return $this->releaseFeedUrl;
    }

    public function setReleaseFeedUrl(string $releaseFeedUrl): static
    {
        $this->releaseFeedUrl = $releaseFeedUrl;

        return $this;
    }

    /**
     * @return Collection<int, Release>
     */
    public function getReleases(): Collection
    {
        return $this->releases;
    }

    public function addRelease(Release $release): static
    {
        if (!$this->releases->contains($release)) {
            $this->releases->add($release);
            $release->setGithubRepo($this);
        }

        return $this;
    }

    public function removeRelease(Release $release): static
    {
        if ($this->releases->removeElement($release)) {
            // set the owning side to null (unless already changed)
            if ($release->getGithubRepo() === $this) {
                $release->setGithubRepo(null);
            }
        }

        return $this;
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
}
