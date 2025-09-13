<?php
//Apellée pour avoir les détails de l'user à partir du token envoyé dans la requete
//Apellée par lAuthProvider pour :
// 1.vérif si user est connecté au chargement de la page
// 2. vérif apr_s refresh du token pour vérifier que le nouveau token est valide et que user est toujours connecté
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(Security $security): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        // On retourne quelques infos utilisateur (exemple : id, email, roles)
        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(), 
            'roles' => $user->getRoles(),
        ]);
    }
}