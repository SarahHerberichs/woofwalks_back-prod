<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch; 
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;



use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;



#[ApiResource(
    //Seule les propriétés qui ont groups user:read seront inclues dans le processus de normalisation
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    validationContext: ['groups' => ['user:write']],
)]
#[Get(
    uriTemplate: '/me', 
    // La route /api/me ne doit être accessible que par un utilisateur authentifié
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    securityMessage: "Authentication required to access user profile.",
    // Ceci est crucial pour définir quelles données de l'utilisateur seront exposées via cette route
    // Assure-toi d'ajouter 'user:me' à toutes les propriétés que tu veux exposer
    normalizationContext: ['groups' => ['user:read', 'user:me']],
)]

#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé pour un autre compte.')]

#[GetCollection(
    // POUR GET /api/users : SEULS LES ADMINS PEUVENT VOIR LA LISTE COMPLÈTE
    security: "is_granted('ROLE_ADMIN')",
    securityMessage: "Accès refusé. Seuls les administrateurs peuvent voir la liste des utilisateurs."
)]
#[Post(
    // POUR POST /api/users : PUBLIC POUR L'INSCRIPTION
    security: "is_granted('PUBLIC_ACCESS')",
    securityMessage: "Accès refusé. L'accès public est requis pour la création d'utilisateurs.",
    processor: \App\DataPersister\UserDataPersister::class,
)]
#[Get(
    // POUR GET /api/users/{id} : ADMIN OU L'UTILISATEUR LUI-MÊME
    security: "is_granted('ROLE_ADMIN') or (is_granted('IS_AUTHENTICATED_FULLY') and object == user)",
    securityMessage: "Accès refusé. Vous devez être administrateur ou consulter votre propre profil."
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:me'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write', 'walk:read', 'user:me'])]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Length(max: 180, maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas une adresse email valide.")]
    private ?string $email = null;

   #[ORM\Column]
    #[Groups(['user:read', 'user:me'])] 
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['user:write'])]
    #[Assert\NotBlank(groups: ['user:write'], message: "Le mot de passe ne peut pas être vide.")]
    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.", groups: ['user:write'])]
    #[Assert\Regex(
        pattern: "/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=<>?]).+$/",
        message: "Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial.",
        groups: ['user:write']
    )]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Walk::class, orphanRemoval: true)]
    private Collection $createdWalks;

    #[ORM\ManyToMany(mappedBy: 'participants', targetEntity: Walk::class)]
    private Collection $participatedWalks;

    #[Groups(['user:read', 'user:write', 'user:me'])]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est requis.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom d'utilisateur ne peut pas dépasser {{ limit }} caractères.")]
    #[ORM\Column(length: 100)]
    private ?string $username = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false; 

    #[ORM\Column(type: 'boolean')]
    #[Groups(['user:write'])]
    #[Assert\NotNull(message: 'Vous devez accepter les CGV')]
    #[Assert\IsTrue(message: 'Vous devez accepter les CGV')]
    private ?bool $cgvAccepted = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: WalkAlertRequest::class, cascade: ['persist', 'remove'])]
    private Collection $walkAlertRequests;

     #[ORM\ManyToMany(targetEntity: Channel::class, mappedBy: 'users')]
    private Collection $channels;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $confirmationRequestedAt = null;

    public function __construct()
    {
        $this->createdWalks = new ArrayCollection();
        $this->participatedWalks = new ArrayCollection();
        $this->walkAlertRequests = new ArrayCollection();
         $this->channels = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user instance.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }
    
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {

    }

    /**
     * @return Collection<int, Walk>
     */
    public function getCreatedWalks(): Collection
    {
        return $this->createdWalks;
    }

    public function addCreatedWalk(Walk $createdWalk): self
    {
        if (!$this->createdWalks->contains($createdWalk)) {
            $this->createdWalks[] = $createdWalk;
            $createdWalk->setCreator($this);
        }
        return $this;
    }

    public function removeCreatedWalk(Walk $createdWalk): self
    {
        if ($this->createdWalks->removeElement($createdWalk)) {
    
            if ($createdWalk->getCreator() === $this) {
                $createdWalk->setCreator(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Walk>
     */
    public function getParticipatedWalks(): Collection
    {
        return $this->participatedWalks;
    }

    public function addParticipatedWalk(Walk $participatedWalk): self
    {
        if (!$this->participatedWalks->contains($participatedWalk)) {
            $this->participatedWalks[] = $participatedWalk;
            $participatedWalk->addParticipant($this);
        }
        return $this;
    }

    public function removeParticipatedWalk(Walk $participatedWalk): self
    {
        if ($this->participatedWalks->removeElement($participatedWalk)) {
            $participatedWalk->removeParticipant($this);
        }
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $token): self
    {
        $this->confirmationToken = $token;
        return $this;
    }

      public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

        public function isCgvAccepted(): ?bool
    {
        return $this->cgvAccepted;
    }

    public function setCgvAccepted(?bool $cgvAccepted): self
    {
        $this->cgvAccepted = $cgvAccepted;
        return $this;
    }

     public function getWalkAlertRequests(): Collection
    {
        return $this->walkAlertRequests;
    }

    public function addWalkAlertRequest(WalkAlertRequest $request): self
    {
        if (!$this->walkAlertRequests->contains($request)) {
            $this->walkAlertRequests[] = $request;
            $request->setUser($this);
        }

        return $this;
    }

    public function removeWalkAlertRequest(WalkAlertRequest $request): self
    {
        if ($this->walkAlertRequests->removeElement($request)) {
            if ($request->getUser() === $this) {
                $request->setUser(null);
            }
        }

        return $this;
    }
     /**
     * @return Collection<int, Channel>
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(Channel $channel): static
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
            $channel->addUser($this);
        }

        return $this;
    }

    public function removeChannel(Channel $channel): static
    {
        if ($this->channels->removeElement($channel)) {
            $channel->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    public function getConfirmationRequestedAt(): ?\DateTimeImmutable
    {
        return $this->confirmationRequestedAt;
    }

    public function setConfirmationRequestedAt(?\DateTimeImmutable $confirmationRequestedAt): self
    {
        $this->confirmationRequestedAt = $confirmationRequestedAt;

        return $this;
    }

}