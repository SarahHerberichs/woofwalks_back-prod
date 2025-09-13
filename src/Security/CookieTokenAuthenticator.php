<?php

namespace App\Security;
// Cet authenticator lit les tokens JWT directement depuis un cookie
// et les valide pour sécuriser les routes de l'API.

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\ChainTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;

class CookieTokenAuthenticator extends AbstractAuthenticator
{
    private ChainTokenExtractor $tokenExtractor;
    private LcobucciJWTEncoder $jwtEncoder;
    private UserProviderInterface $userProvider;

    public function __construct(
        ChainTokenExtractor $tokenExtractor,
        LcobucciJWTEncoder $jwtEncoder,
        UserProviderInterface $userProvider
    ) {
        $this->tokenExtractor = $tokenExtractor;
        $this->jwtEncoder = $jwtEncoder;
        $this->userProvider = $userProvider;
    }

    // Détermine si l'authenticator doit être exécuté.
    public function supports(Request $request): ?bool
    {
        // Routes publiques ou l'authentification n'est pas requise.
        $publicPaths = [
            '^/api/confirm-email/',
            '^/api/logout/',
            '^/api/users(\.json)?$',
            '^/api/token/refresh',
            '^/api/walks/?$',
            '^/api/parks/?$',
            '^/api/(docs|contexts)',
            '^/$',
            
        ];

        $path = $request->getPathInfo();
        foreach ($publicPaths as $pattern) {
            if (preg_match("#$pattern#", $path)) {
                // error_log("⛔ CookieTokenAuthenticator ignoré, route publique : $path");
                return false;
            }
        }

        //Utilisation de la chaîne d'extracteurs pour vérifier la présence d'un token.
        return $this->tokenExtractor->extract($request) !== null;
    }

    //Logique d'authentification pricnipale
    public function authenticate(Request $request): Passport
    {
        //Utilisation de la chaîne d'extracteurs pour vérifier la présence d'un token.
        $token = $this->tokenExtractor->extract($request);
        
        if (!$token) {
            throw new CustomUserMessageAuthenticationException('Aucun token JWT dans la requête.');
        }

        try {
            // Décodage du token pour extraire les données.
            $data = $this->jwtEncoder->decode($token);
            if (!$data) {
                throw new CustomUserMessageAuthenticationException('Token invalide.');
            }
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Token invalide ou mal formé.');
        }

        if (!isset($data['email'])) {
            throw new CustomUserMessageAuthenticationException('Token JWT invalide ou email absent.');
        }

        // Chargement de l'utilisateur à partir de l'email contenu dans le token.
        return new SelfValidatingPassport(new UserBadge($data['email'], function ($userIdentifier) {
            return $this->userProvider->loadUserByIdentifier($userIdentifier);
        }));
    }

    // Appelé en cas de succès de l'authentification --Rien
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    // Appelé en cas d'échec de l'authentification.
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response(json_encode(['error' => $exception->getMessage()]), 401, ['Content-Type' => 'application/json']);
    }
}
