<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailConfirmationController extends AbstractController
{
    #[Route('/api/confirm-email/{token}', name: 'email_confirmation', methods: ['GET'])]
    public function confirmEmail(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {

        $user = $userRepository->findOneBy(['confirmationToken' => $token]);
        // Utilisateur pas trouvé 
        if (!$user) {
            // Si $user est null, le token est invalide (n'existe pas ou a déjà été utilisé).
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Lien de confirmation invalide ou déjà utilisé.'
            ], 400); // Bad Request car le token n'est pas valide ou est expiré
        }

        //Vérifie si $user, trouvé est DEJA marqué comme vérifié.(Si probleme incohérence donnees)
        if ($user->isVerified()) {
            return new JsonResponse([
                'status' => 'info',
                'message' => 'Votre adresse email est déjà vérifiée.'
            ], 200); 
        }
        //SI la date de la création de compte, était il y a + de 24 h 
        if ($user->getConfirmationRequestedAt() < (new \DateTimeImmutable())->modify('-24 hours')) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Ce lien de confirmation a expiré.'
        ], 400);
    }

        
        $user->setConfirmationToken(null);
        $user->setIsVerified(true);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Adresse email confirmée. Vous pouvez maintenant vous connecter.'
        ]);
    }
}