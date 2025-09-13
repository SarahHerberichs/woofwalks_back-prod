<?php
// src/Security/RefreshTokenAuthenticator.php

namespace App\Security;

use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\HttpFoundation\JsonResponse;

class RefreshTokenAuthenticator extends AbstractAuthenticator
{
    private EntityManagerInterface $em;
    private UserProviderInterface $userProvider;

    public function __construct(EntityManagerInterface $em, UserProviderInterface $userProvider)
    {
        $this->em = $em;
        $this->userProvider = $userProvider;
    }

    // Cette méthode détermine si l'authenticator doit être exécuté pour cette requête
    public function supports(Request $request): ?bool
    {
        return $request->cookies->has('REFRESH_TOKEN') && $request->getPathInfo() === '/api/token/refresh';
    }

    // Cette méthode contient la logique d'authentification
    public function authenticate(Request $request): Passport
    {
        $refreshTokenValue = $request->cookies->get('REFRESH_TOKEN');
        if (!$refreshTokenValue) {
            throw new CustomUserMessageAuthenticationException('Missing refresh token.');
        }

        $token = $this->em->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $refreshTokenValue]);

        if (!$token || $token->getValid() < new \DateTime()) {
            throw new CustomUserMessageAuthenticationException('Invalid or expired refresh token.');
        }

        $user = $this->userProvider->loadUserByIdentifier($token->getUsername());

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('User not found.');
        }

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => 'Authentication failed: ' . $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }
}
