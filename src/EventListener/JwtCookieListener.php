<?php

namespace App\EventListener;
//Après  un login_check ou un refresh
//- Récupère le token jwt de la réponse - l'injecte dans un cookie -supprime le token du corps json de la réponse

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

class JwtCookieListener {
    public function onKernelResponse(ResponseEvent $event): void {
        // error_log('🔍 JwtCookieListener appelé');
        $request = $event->getRequest();
        //Routes ou le listener doit s'executer
        $allowedPaths = [
        '/api/login_check',
        '/api/token/refresh',
        '/api/walks'
        ];

        if (!in_array($request->getPathInfo(), $allowedPaths)) {
            // error_log('⛔ JwtCookieListener ignoré, route incorrecte: ' . $request->getPathInfo());
            return;
        }
        // Récupère la Réponse
        $response = $event->getResponse();

        // Décode le contenu JSON de la réponse (pour accéder au token)
        $content = json_decode($response->getContent(), true);
        
        // Vérifie si un token JWT est présent dans la réponse -- Si c'est le cas, va creer deux cookies
        //ce n'est normalement pas le cas dans le cadre d'une sortie de tokenrefreshcontroller mais c'est le cas lors
        //de la connexion
        if (isset($content['token'])) {
            // Récupère le token JWT
            $token = $content['token'];

            // Crée un cookie nommé "BEARER" contenant le token
            $cookie = Cookie::create('BEARER', $token)
                ->withHttpOnly(true) // Le cookie ne sera pas accessible en JavaScript (sécurité XSS)
                ->withSecure(true) 
                ->withSameSite('Lax') // Protège un peu contre les attaques CSRF
                ->withPath('/');     // Le cookie sera envoyé pour toutes les requêtes sur le site

            // Ajout du cookie à l’en-tête de la réponse HTTP
            $response->headers->setCookie($cookie);

            // Émet un cookie CSRF non-HttpOnly pour double-submit (lu par le frontend)
            $csrfToken = bin2hex(random_bytes(32));
            $xsrfCookie = Cookie::create('XSRF-TOKEN', $csrfToken)
                ->withHttpOnly(false)
                ->withSecure(true)
                ->withSameSite('Lax')
                ->withPath('/');
            $response->headers->setCookie($xsrfCookie);

            // Supprime le token du corps de la réponse (pour éviter qu’il soit accessible côté frontend)
            unset($content['token']);

            // Remplace le contenu JSON de la réponse par le nouveau contenu sans le token
            $response->setContent(json_encode($content));
        }
        if (isset($content['refresh_token'])) {
            $refreshToken = $content['refresh_token'];
            $cookieRefresh = Cookie::create('REFRESH_TOKEN', $refreshToken)
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withSameSite('Lax')
                ->withPath('/');
            $response->headers->setCookie($cookieRefresh);

            // Émet/renouvelle le cookie CSRF côté refresh
            $csrfToken = bin2hex(random_bytes(32));
            $xsrfCookie = Cookie::create('XSRF-TOKEN', $csrfToken)
            //Accessible au JS! Le front end peut le lire et le mettre dans un header 
                ->withHttpOnly(false)
                ->withSecure(true)
                ->withSameSite('Lax')
                ->withPath('/');
            $response->headers->setCookie($xsrfCookie);

            unset($content['refresh_token']);
            
            $response->setContent(json_encode($content));
        }

    }
}

