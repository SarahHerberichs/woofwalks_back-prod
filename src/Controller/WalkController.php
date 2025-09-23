<?php

namespace App\Controller;

use App\Service\WalkCreationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Contract\WalkWithChatCreationInterface;
use App\Service\WalkValidationService;

class WalkController extends AbstractController {
    #[Route('/api/walkscustom', name: 'create_walk', methods: ['POST'])]
    public function createWalk(
        Request $request,
        WalkWithChatCreationInterface $walkCreator,
        WalkValidationService $validationService 

        ): JsonResponse {

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], 401);
        }
        $data = json_decode($request->getContent(), true);
  
        // Validation centralisée par le Service validation
        $validationResult = $validationService->validateWalkData($data);

        if (!$validationResult->isValid()) {
            return new JsonResponse(['errors' => $validationResult->getErrors()], 400);
        }
        //Passe par Interface pour apeller Service de Création
        $walk = $walkCreator->createWalkWithChat($data);

        if (!$walk) {
            return new JsonResponse(['error' => 'Failed to create walk (invalid data or dependencies)'], 400);
        }

        return new JsonResponse(['message' => 'Walk created successfully'], 201);
    }
}

