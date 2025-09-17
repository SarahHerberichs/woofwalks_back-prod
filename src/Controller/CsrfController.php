<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CsrfController extends AbstractController
{
    #[Route('/api/csrf-token', name: 'api_csrf_token', methods: ['GET'])]
    public function getCsrfToken(): JsonResponse
    {
        // Génère un token CSRF aléatoire
        $csrfToken = bin2hex(random_bytes(32));
        
        // Stocke le token en session (sécurisé côté serveur)
        $this->get('session')->set('csrf_token', $csrfToken);
        
        return new JsonResponse(['csrf_token' => $csrfToken]);
    }
}
