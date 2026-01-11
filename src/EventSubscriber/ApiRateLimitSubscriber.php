<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiRateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $anonymousApiLimiter,
        private RateLimiterFactory $authenticatedApiLimiter,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -10],
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Ne s'applique qu'aux routes API
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        // Exclure la documentation API
        if (str_starts_with($request->getPathInfo(), '/api/docs') ||
            str_starts_with($request->getPathInfo(), '/api/graphql/graphiql')) {
            return;
        }

        // Vérifier si l'utilisateur est authentifié via JWT
        $token = $this->tokenStorage->getToken();
        $isAuthenticated = $token && $token->getUser() instanceof UserInterface;

        // Identifier l'utilisateur (email si authentifié, IP sinon)
        $identifier = $isAuthenticated
            ? $token->getUser()->getUserIdentifier()
            : $request->getClientIp() ?? 'unknown';

        // Sélectionner le rate limiter approprié
        $limiter = $isAuthenticated
            ? $this->authenticatedApiLimiter
            : $this->anonymousApiLimiter;

        // Consommer un token
        $limit = $limiter->create($identifier)->consume();

        // Stocker les informations de rate limit dans les attributs de la requête
        $request->attributes->set('_rate_limit', [
            'limit' => $limit->getLimit(),
            'remaining' => $limit->getRemainingTokens(),
            'reset' => $limit->getRetryAfter()->getTimestamp(),
        ]);

        // Si la limite est dépassée, retourner une erreur 429
        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp();

            $response = new JsonResponse([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429);

            $response->headers->set('Retry-After', (string) $retryAfter);
            $response->headers->set('X-RateLimit-Limit', (string) $limit->getLimit());
            $response->headers->set('X-RateLimit-Remaining', '0');
            $response->headers->set('X-RateLimit-Reset', (string) $retryAfter);

            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Récupérer les informations de rate limit depuis les attributs de la requête
        $rateLimitInfo = $request->attributes->get('_rate_limit');

        if (!$rateLimitInfo) {
            return;
        }

        // Ajouter les headers de rate limit à la réponse
        $response->headers->set('X-RateLimit-Limit', (string) $rateLimitInfo['limit']);
        $response->headers->set('X-RateLimit-Remaining', (string) $rateLimitInfo['remaining']);
        $response->headers->set('X-RateLimit-Reset', (string) $rateLimitInfo['reset']);
    }
}
