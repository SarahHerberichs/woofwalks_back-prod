<?php

namespace App\Service\Contract;

use App\Entity\Walk;

interface WalkWithChatCreationInterface {
    public function createWalkWithChat(array $data): ?Walk;
    //POssibilité de creer createWalkOnly ou addChatToExistingWalk
}
