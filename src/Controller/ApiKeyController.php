<?php

namespace App\Controller;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/api-keys')]
#[IsGranted('ROLE_ADMIN')]
class ApiKeyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApiKeyRepository $apiKeyRepository,
        private UserRepository $userRepository
    ) {
    }

    #[Route('', name: 'api_key_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userId'])) {
            return new JsonResponse(['error' => 'userId is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find($data['userId']);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Generate API key
        $plainApiKey = $this->generateApiKey();
        $prefix = substr($plainApiKey, 0, 16);
        $salt = bin2hex(random_bytes(400)); // 800 chars hex = 400 bytes
        $hash = substr(hash('sha256', $plainApiKey . $salt), 0, 34);

        // Create ApiKey entity
        $apiKey = new ApiKey();
        $apiKey->setUser($user);
        $apiKey->setApiKeyPrefix($prefix);
        $apiKey->setApiKeySalt($salt);
        $apiKey->setApiKeyHash($hash);
        $apiKey->setIsActive(true);

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        // Return the plain key ONLY ONCE
        return new JsonResponse([
            'id' => $apiKey->getId(),
            'apiKey' => $plainApiKey, // Return plain key only once
            'prefix' => $prefix,
            'userId' => $user->getId(),
            'createdAt' => $apiKey->getApiKeyCreatedAt()->format('Y-m-d H:i:s'),
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'api_key_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $apiKeys = $this->apiKeyRepository->findAll();

        $data = array_map(function (ApiKey $apiKey) {
            return [
                'id' => $apiKey->getId(),
                'prefix' => $apiKey->getApiKeyPrefix(),
                'userId' => $apiKey->getUser()->getId(),
                'userEmail' => $apiKey->getUser()->getEmail(),
                'isActive' => $apiKey->isActive(),
                'createdAt' => $apiKey->getApiKeyCreatedAt()->format('Y-m-d H:i:s'),
                'lastUsedAt' => $apiKey->getApiKeyLastUsedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $apiKeys);

        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'api_key_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $apiKey = $this->apiKeyRepository->find($id);

        if (!$apiKey) {
            return new JsonResponse(['error' => 'API Key not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $apiKey->getId(),
            'prefix' => $apiKey->getApiKeyPrefix(),
            'userId' => $apiKey->getUser()->getId(),
            'userEmail' => $apiKey->getUser()->getEmail(),
            'isActive' => $apiKey->isActive(),
            'createdAt' => $apiKey->getApiKeyCreatedAt()->format('Y-m-d H:i:s'),
            'lastUsedAt' => $apiKey->getApiKeyLastUsedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/{id}', name: 'api_key_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $apiKey = $this->apiKeyRepository->find($id);

        if (!$apiKey) {
            return new JsonResponse(['error' => 'API Key not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['isActive'])) {
            $apiKey->setIsActive((bool) $data['isActive']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $apiKey->getId(),
            'prefix' => $apiKey->getApiKeyPrefix(),
            'isActive' => $apiKey->isActive(),
        ]);
    }

    #[Route('/{id}', name: 'api_key_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $apiKey = $this->apiKeyRepository->find($id);

        if (!$apiKey) {
            return new JsonResponse(['error' => 'API Key not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($apiKey);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'API Key deleted'], Response::HTTP_NO_CONTENT);
    }

    private function generateApiKey(): string
    {
        // Generate a random API key (e.g., 32 bytes = 64 hex chars)
        return bin2hex(random_bytes(32));
    }
}
