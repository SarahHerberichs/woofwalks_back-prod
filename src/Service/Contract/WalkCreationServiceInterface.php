<?php

namespace App\Service\Contract;

use App\Entity\Walk;

interface WalkCreationServiceInterface
{
    public function createWalkAndChat(array $data): ?Walk;
}
