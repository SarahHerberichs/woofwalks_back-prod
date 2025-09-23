<?php

namespace App\Service;

use App\Entity\Chat;
use App\Entity\Walk;
use Doctrine\ORM\EntityManagerInterface;

class ChatCreationService {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function createChatForWalk(Walk $walk): Chat{
        $chat = new Chat();
        $chat->setWalk($walk);
        
        // Établir la relation bidirectionnelle
        $walk->setChat($chat);

        $this->entityManager->persist($chat);
        $this->entityManager->flush();

        return $chat;
    }
}
