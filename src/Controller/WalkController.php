<?php

namespace App\Controller;

use App\Service\WalkCreationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Contract\WalkCreationServiceInterface;

class WalkController extends AbstractController {
    #[Route('/api/walkscustom', name: 'create_walk', methods: ['POST'])]
    public function createWalk(Request $request, WalkCreationServiceInterface $walkCreationServiceInterface): JsonResponse
    {
          $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'Authentication required'], 401);
            }
        $data = json_decode($request->getContent(), true);
  
        // Validation basique dans le contrôleur
        if (
            empty($data['title']) ||
            empty($data['description']) ||
            empty($data['datetime']) ||
            empty($data['photo']) || 
            empty($data['location']) ||
            !isset($data['is_custom_location']) || 
            !is_bool($data['is_custom_location']) ||
            !isset($data['max_participants']) 
        ) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $walk = $walkCreationServiceInterface->createWalkAndChat($data);

        if (!$walk) {
            return new JsonResponse(['error' => 'Failed to create walk (invalid data or dependencies)'], 400);
        }

        return new JsonResponse(['message' => 'Walk created successfully'], 201);
    }
}