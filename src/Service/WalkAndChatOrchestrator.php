<?php

namespace App\Service;

use App\Entity\Walk;
use App\Service\Contract\WalkWithChatCreationInterface;

class WalkAndChatOrchestrator implements WalkWithChatCreationInterface {
    private WalkCreationService $walkCreationService;
    private ChatCreationService $chatCreationService;

    public function __construct(
        WalkCreationService $walkCreationService,
        ChatCreationService $chatCreationService
    ) {
        $this->walkCreationService = $walkCreationService;
        $this->chatCreationService = $chatCreationService;
    }

    public function createWalkWithChat(array $data): ?Walk {
        // Création de la Walk
        $walk = $this->walkCreationService->createWalk($data);
        
        if (!$walk) {
            return null;
        }
        //Création du Chat associé
        $this->chatCreationService->createChatForWalk($walk);

        return $walk;
    }
}
