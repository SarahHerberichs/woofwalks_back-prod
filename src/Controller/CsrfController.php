<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CsrfController extends AbstractController
{
    #[Route('/api/csrf-token', name: 'api_csrf_token', methods: ['GET'])]
    public function getCsrfToken(Request $request): JsonResponse
    {
        // Démarre la session si elle n'est pas déjà démarrée
        if (!$request->hasSession()) {
            $request->setSession($this->get('session'));
        }
        
        // Génère un token CSRF aléatoire
        $csrfToken = bin2hex(random_bytes(32));
        
        // Stocke le token en session (sécurisé côté serveur)
        $request->getSession()->set('csrf_token', $csrfToken);
        
        return new JsonResponse(['csrf_token' => $csrfToken]);
    }
}
