<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['walk_alert_request:read']],
    denormalizationContext: ['groups' => ['walk_alert_request:write']]
)]

class WalkAlertRequest
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'walkAlertRequests')]
    #[Groups(['walk_alert_request:read', 'walk_alert_request:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Walk::class, inversedBy: 'alertRequests')]
    #[Groups(['walk_alert_request:read', 'walk_alert_request:write'])]
    private ?Walk $walk = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['walk_alert_request:read', 'walk_alert_request:write'])]    
    private \DateTimeInterface $requestedAt;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['walk_alert_request:read', 'walk_alert_request:write'])]    
    private bool $notified = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getWalk(): ?Walk
    {
        return $this->walk;
    }

    public function setWalk(?Walk $walk): self
    {
        $this->walk = $walk;

        return $this;
    }

    public function getRequestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $requestedAt): self
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function isNotified(): bool
    {
        return $this->notified;
    }

    public function setNotified(bool $notified): self
    {
        $this->notified = $notified;

        return $this;
    }
}
