<?php

namespace App\EventListener;
//Déclenché après la génération du JWT par lexik. 
//Ce Listener est abonné à "authenticationSuccessEvent:lexik"
//Ajout d'un token refreshtoken en plus de celui de lexik déjà crée et le place l'ensemble de la data (les 2 token) dans la response
//JWTCookieListener prend le relai pour en créer des Cookies et unset cette data
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class AuthenticationSuccessListener
{
     private EntityManagerInterface $em;
    private int $refreshTokenTtl; 

    public function __construct(EntityManagerInterface $em, int $refreshTokenTtl)
    {
        $this->em = $em;
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    //En plus du token lexik, ajout du refreshtoken 
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event) {
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        // Génération du refresh token (UUID par exemple)
        $newRefreshTokenString = bin2hex(random_bytes(40)); // 80 caractères hexadécimaux aléatoires

        $validityDate = new \DateTime();
        $validityDate->modify('+' . $this->refreshTokenTtl . ' seconds');

        // Création de l'entité RefreshToken
        $refreshToken = new RefreshToken();
        $refreshToken->setRefreshToken($newRefreshTokenString);
        $refreshToken->setUsername($user->getEmail());
        $refreshToken->setValid($validityDate);


        $this->em->persist($refreshToken);
        $this->em->flush();

        // Récupération de la réponse actuelle
        $data = $event->getData();

        // Ajout du refresh_token dans la réponse JSON
        $data['refresh_token'] = $newRefreshTokenString;

        $event->setData($data);
    }
}
