<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ChatMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch; 
use ApiPlatform\Metadata\GetCollection;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ApiResource(
    normalizationContext: ['groups' => ['message:read']],
    denormalizationContext: ['groups' => ['message:write']],
)]
#[GetCollection(
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
    securityMessage: "Vous devez être connecté pour voir les messages."
)]
#[Get(
    security: "is_granted('ROLE_ADMIN') or object.sender == user or object.chat.walk.participants.contains(user)",
    securityMessage: "Vous ne pouvez voir ce message que si vous en êtes l'auteur ou participant du chat."
)]
#[Post(
    security: "is_granted('ROLE_USER')",
    securityMessage: "Vous devez être connecté pour envoyer un message."
)]
#[Delete(
    security: "is_granted('ROLE_ADMIN') or object.sender == user",
    securityMessage: "Seul l'auteur ou un admin peut supprimer ce message."
)]
#[ORM\Entity(repositoryClass: ChatMessageRepository::class)]


class ChatMessage {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['message:read', 'chat:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:read', 'message:write', 'chat:read'])]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: Chat::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:read', 'message:write'])]
    private ?Chat $chat = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['message:read', 'message:write', 'chat:read'])]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['message:read', 'chat:read'])]
    private ?\DateTimeInterface $timestamp = null;

    public function __construct()
    {
        $this->timestamp = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }
}

