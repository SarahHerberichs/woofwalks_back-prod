<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ChatRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['chat:read']],
    denormalizationContext: ['groups' => ['chat:write']],
    security: "is_granted('ROLE_ADMIN') or object.walk.participants.contains(user)",
    securityMessage: "Vous devez être participant de la walk pour accéder à ce chat."
)]

#[ORM\Entity(repositoryClass: ChatRepository::class)]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'chat', targetEntity: Walk::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['chat:read', 'chat:write'])]
    private ?Walk $walk = null;

    #[ORM\OneToMany(mappedBy: 'chat', targetEntity: ChatMessage::class, cascade: ['persist', 'remove'])]
    #[Groups(['chat:read'])]
    private Collection $chatMessages;

    public function __construct()
    {
        $this->chatMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWalk(): ?Walk
    {
        return $this->walk;
    }

    public function setWalk(Walk $walk): self
    {
        $this->walk = $walk;
        return $this;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getChatMessages(): Collection
    {
        return $this->chatMessages;
    }

    public function addChatMessage(ChatMessage $chatMessage): self
    {
        if (!$this->chatMessages->contains($chatMessage)) {
            $this->chatMessages[] = $chatMessage;
            $chatMessage->setChat($this);
        }
        return $this;
    }

    public function removeMessage(ChatMessage $chatMessage): self
    {
        if ($this->chatMessages->removeElement($chatMessage)) {
            if ($chatMessage->getChat() === $this) {
                $chatMessage->setChat(null);
            }
        }
        return $this;
    }
}