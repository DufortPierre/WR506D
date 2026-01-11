<?php

namespace App\Security;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ApiKeyRepository $apiKeyRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-API-Key');
    }

    public function authenticate(Request $request): Passport
    {
        $apiKeyValue = $request->headers->get('X-API-Key');

        if (null === $apiKeyValue || '' === $apiKeyValue) {
            throw new CustomUserMessageAuthenticationException('API Key header is missing.');
        }

        // Extract prefix (first 16 characters)
        if (strlen($apiKeyValue) < 16) {
            throw new CustomUserMessageAuthenticationException('Invalid API Key format.');
        }

        $prefix = substr($apiKeyValue, 0, 16);

        // Find API key by prefix
        $apiKey = $this->apiKeyRepository->findOneBy(['apiKeyPrefix' => $prefix]);

        if (!$apiKey) {
            throw new CustomUserMessageAuthenticationException('Invalid API Key.');
        }

        // Check if API key is active
        if (!$apiKey->isActive()) {
            throw new CustomUserMessageAuthenticationException('API Key is inactive.');
        }

        // Verify the full key by hashing with salt
        $fullKeyHash = hash('sha256', $apiKeyValue . $apiKey->getApiKeySalt());
        $computedHash = substr($fullKeyHash, 0, 34);

        if (!hash_equals($apiKey->getApiKeyHash(), $computedHash)) {
            throw new CustomUserMessageAuthenticationException('Invalid API Key.');
        }

        // Update last used timestamp
        $apiKey->setApiKeyLastUsedAt(new DateTimeImmutable());
        $this->entityManager->flush();

        // Return passport with user
        return new SelfValidatingPassport(
            new UserBadge($apiKey->getUser()->getUserIdentifier(), function () use ($apiKey) {
                return $apiKey->getUser();
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Continue with the request
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => 'Authentication Failed',
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
