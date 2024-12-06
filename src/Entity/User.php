<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, PushSubscription>
     */
    #[ORM\OneToMany(targetEntity: PushSubscription::class, mappedBy: 'user')]
    private Collection $pushSubscriptions;

    /**
     * @var Collection<int, Tool>
     */
    #[ORM\OneToMany(targetEntity: Tool::class, mappedBy: 'user')]
    private Collection $tools;

    /**
     * @var Collection<int, RSSFeed>
     */
    #[ORM\OneToMany(targetEntity: RSSFeed::class, mappedBy: 'user')]
    private Collection $rSSFeeds;

    /**
     * @var Collection<int, GithubRepository>
     */
    #[ORM\OneToMany(targetEntity: GithubRepository::class, mappedBy: 'user')]
    private Collection $githubRepositories;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'user')]
    private Collection $categories;

    /**
     * @var Collection<int, YoutubeChannel>
     */
    #[ORM\OneToMany(targetEntity: YoutubeChannel::class, mappedBy: 'user')]
    private Collection $youtubeChannels;

    public function __construct()
    {
        $this->pushSubscriptions = new ArrayCollection();
        $this->tools = new ArrayCollection();
        $this->rSSFeeds = new ArrayCollection();
        $this->githubRepositories = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->youtubeChannels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, PushSubscription>
     */
    public function getPushSubscriptions(): Collection
    {
        return $this->pushSubscriptions;
    }

    public function addPushSubscription(PushSubscription $pushSubscription): static
    {
        if (!$this->pushSubscriptions->contains($pushSubscription)) {
            $this->pushSubscriptions->add($pushSubscription);
            $pushSubscription->setUser($this);
        }

        return $this;
    }

    public function removePushSubscription(PushSubscription $pushSubscription): static
    {
        if ($this->pushSubscriptions->removeElement($pushSubscription)) {
            // set the owning side to null (unless already changed)
            if ($pushSubscription->getUser() === $this) {
                $pushSubscription->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tool>
     */
    public function getTools(): Collection
    {
        return $this->tools;
    }

    public function addTool(Tool $tool): static
    {
        if (!$this->tools->contains($tool)) {
            $this->tools->add($tool);
            $tool->setUser($this);
        }

        return $this;
    }

    public function removeTool(Tool $tool): static
    {
        if ($this->tools->removeElement($tool)) {
            // set the owning side to null (unless already changed)
            if ($tool->getUser() === $this) {
                $tool->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RSSFeed>
     */
    public function getRSSFeeds(): Collection
    {
        return $this->rSSFeeds;
    }

    public function addRSSFeed(RSSFeed $rSSFeed): static
    {
        if (!$this->rSSFeeds->contains($rSSFeed)) {
            $this->rSSFeeds->add($rSSFeed);
            $rSSFeed->setUser($this);
        }

        return $this;
    }

    public function removeRSSFeed(RSSFeed $rSSFeed): static
    {
        if ($this->rSSFeeds->removeElement($rSSFeed)) {
            // set the owning side to null (unless already changed)
            if ($rSSFeed->getUser() === $this) {
                $rSSFeed->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GithubRepository>
     */
    public function getGithubRepositories(): Collection
    {
        return $this->githubRepositories;
    }

    public function addGithubRepository(GithubRepository $githubRepository): static
    {
        if (!$this->githubRepositories->contains($githubRepository)) {
            $this->githubRepositories->add($githubRepository);
            $githubRepository->setUser($this);
        }

        return $this;
    }

    public function removeGithubRepository(GithubRepository $githubRepository): static
    {
        if ($this->githubRepositories->removeElement($githubRepository)) {
            // set the owning side to null (unless already changed)
            if ($githubRepository->getUser() === $this) {
                $githubRepository->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, YoutubeChannel>
     */
    public function getYoutubeChannels(): Collection
    {
        return $this->youtubeChannels;
    }

    public function addYoutubeChannel(YoutubeChannel $youtubeChannel): static
    {
        if (!$this->youtubeChannels->contains($youtubeChannel)) {
            $this->youtubeChannels->add($youtubeChannel);
            $youtubeChannel->setUser($this);
        }

        return $this;
    }

    public function removeYoutubeChannel(YoutubeChannel $youtubeChannel): static
    {
        if ($this->youtubeChannels->removeElement($youtubeChannel)) {
            // set the owning side to null (unless already changed)
            if ($youtubeChannel->getUser() === $this) {
                $youtubeChannel->setUser(null);
            }
        }

        return $this;
    }
}
