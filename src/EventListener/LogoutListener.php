<?php


namespace App\EventListener;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class LogoutListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onLogout(LogoutEvent $event): void
    {
        // 1. Gestion de  l'invalidation côté serveur
        $user = $event->getToken()?->getUser();

        // vérif que utilisateur bien associé à la session de deconnexion
        if ($user instanceof User) {
            // Cherche le refresh token associé à cet utilisateur en BDD.
            $refreshToken = $this->em->getRepository(RefreshToken::class)->findOneBy(['username' => $user->getUserIdentifier()]);

            // S'il existe,suppression
            if ($refreshToken) {
                $this->em->remove($refreshToken);
                $this->em->flush();
            }
        }

        // 2.Gestion de l'invalidation côté client (la partie déjà existante)
        $response = new JsonResponse(['message' => 'Déconnexion réussie']);
        
        // Création de cookies avec une date d'expiration dans le passé pour les invalider.
        $expiredBearerCookie = Cookie::create('BEARER', '', new \DateTime('-1 year'))
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite('Lax')
            ->withPath('/');
        $expiredRefreshCookie = Cookie::create('REFRESH_TOKEN', '', new \DateTime('-1 year'))
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite('Lax')
            ->withPath('/');

        // Invalide le cookie CSRF non-HttpOnly
        $expiredXsrfCookie = Cookie::create('XSRF-TOKEN', '', new \DateTime('-1 year'))
            ->withHttpOnly(false)
            ->withSecure(true)
            ->withSameSite('Lax')
            ->withPath('/');

        $response->headers->setCookie($expiredBearerCookie);
        $response->headers->setCookie($expiredRefreshCookie);
        $response->headers->setCookie($expiredXsrfCookie);
        
        $event->setResponse($response);
    }
}
// namespace App\EventListener;

// use App\Entity\RefreshToken;
// use App\Entity\User;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\HttpFoundation\Cookie;
// use Symfony\Component\Security\Http\Event\LogoutEvent;
// use Symfony\Component\Security\Core\User\UserInterface;

// class LogoutListener
// {
//     private EntityManagerInterface $em;

//     public function __construct(EntityManagerInterface $em)
//     {
//         $this->em = $em;
//     }

//     public function onLogout(LogoutEvent $event): void
//     {
//         // 1. Gestion de  l'invalidation côté serveur
//         $user = $event->getToken()?->getUser();

//         // vérif que utilisateur bien associé à la session de deconnexion
//         if ($user instanceof User) {
//             // Cherche le refresh token associé à cet utilisateur en BDD.
//             $refreshToken = $this->em->getRepository(RefreshToken::class)->findOneBy(['username' => $user->getUserIdentifier()]);

//             // S'il existe,suppression
//             if ($refreshToken) {
//                 $this->em->remove($refreshToken);
//                 $this->em->flush();
//             }
//         }

//         // 2.Gestion de l'invalidation côté client (la partie déjà existante)
//         $response = new JsonResponse(['message' => 'Déconnexion réussie']);
        
//         // Création de cookies avec une date d'expiration dans le passé pour les invalider.
//         $expiredBearerCookie = Cookie::create('BEARER', '', new \DateTime('-1 year'));
//         $expiredRefreshCookie = Cookie::create('REFRESH_TOKEN', '', new \DateTime('-1 year'));

//         $response->headers->setCookie($expiredBearerCookie);
//         $response->headers->setCookie($expiredRefreshCookie);
        
//         $event->setResponse($response);
//     }
// }