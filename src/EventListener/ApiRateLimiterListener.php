<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class ApiRateLimiterListener
{
    private RateLimiterFactory $limiterFactory;
    private Security $security;
    private LoggerInterface $logger;

    public function __construct(RateLimiterFactory $limiterFactory, Security $security, LoggerInterface $logger) {
        $this->limiterFactory = $limiterFactory;
        $this->security = $security;
        $this->logger = $logger;
    }
    public function onKernelRequest(RequestEvent $event): void {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $user = $this->security->getUser();
        $key = $user ? 'user_'.$user->getId() : 'ip_'.$request->getClientIp();

        $limiter = $this->limiterFactory->create($key);
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()?->getTimestamp() ?: 60;

            $response = new JsonResponse(
                ['error' => 'Too many requests, please wait before retrying.'],
                429
            );
            $response->headers->set('Retry-After', $retryAfter);

            $event->setResponse($response); 
        }
    }

}
