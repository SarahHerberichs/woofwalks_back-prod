<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;

class CsrfRequestListener {
    /**
     * Valide l'en-tête X-CSRF-Token par rapport au cookie XSRF-TOKEN
     * pour les méthodes mutatives. -- Erreur s'ils ne sont pas valides
     */
    public function onKernelRequest(RequestEvent $event): void {
    
        $request = $event->getRequest();

        // Exemptions
        if ($request->getMethod() === 'OPTIONS') {
            return;
        }
        $path = $request->getPathInfo();
        $publicRoutes = ['/api/walks', '/api/login_check', '/api/token/refresh', '/api/logout', '/api/me'];
        //Si route publique, stop execution
        if (in_array($path, $publicRoutes)) {
            return;
        }

        // 2. Si pas des méthodes pas mutatives - stop execution
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }
        //Récupère CSRF et message d'erreur si invalide
        $cookieToken = $request->cookies->get('XSRF-TOKEN');
        $headerToken = $request->headers->get('X-CSRF-Token');

        if (!$cookieToken || !$headerToken || !hash_equals($cookieToken, $headerToken)) {
            $response = new Response(json_encode(['message' => 'CSRF token invalide ou manquant']), 403, ['Content-Type' => 'application/json']);
            $event->setResponse($response);
        }
    }
}
