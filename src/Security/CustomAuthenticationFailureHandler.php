<?php


namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomAuthenticationFailureHandler extends AuthenticationFailureHandler {
    private RateLimiterFactory $loginLimiter;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ?TranslatorInterface $translator,
        JWTManagerInterface $jwtManager,
        LoggerInterface $logger,
        RateLimiterFactory $loginLimiter
    ) {
        parent::__construct($dispatcher, $translator, $jwtManager, $logger);
        $this->loginLimiter = $loginLimiter;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response {
        try {
            $limiter = $this->loginLimiter->create($request->getClientIp());

            if (!$limiter->consume()->isAccepted()) {
                // On renvoie un 429 si la limite est dépassée
                return new JsonResponse(
                    ['message' => 'Trop de tentatives de connexion, veuillez réessayer plus tard.'],
                    Response::HTTP_TOO_MANY_REQUESTS
                );
            }
        } catch (\Exception $e) {
            // Cette partie du code attrape l'erreur et empêche le 500
            $this->logger->error(sprintf(
                'Erreur critique dans le rate limiter : %s',
                $e->getMessage()
            ));

            // On renvoie une réponse appropriée au lieu d'un 500
            return new JsonResponse(
                ['message' => 'Service d\'authentification temporairement indisponible.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if ($exception instanceof CustomUserMessageAccountStatusException) {
            return new JsonResponse(
                ['message' => $exception->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        }
               // Gère les exceptions d'identifiants incorrects
        if ($exception instanceof BadCredentialsException) {
            return new JsonResponse(
                ['message' => 'L\'email ou le mot de passe que vous avez saisi est incorrect.'], 
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Gère les exceptions d'utilisateur non trouvé
        if ($exception instanceof UserNotFoundException) {
            return new JsonResponse(
                ['message' => 'L\'email que vous avez saisi est incorrect.'], 
                Response::HTTP_UNAUTHORIZED
            );
        }
        

        return parent::onAuthenticationFailure($request, $exception);
    }
}