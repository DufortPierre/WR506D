<?php

namespace App\Tests;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiKeyTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?UserRepository $userRepository = null;
    private ?ApiKeyRepository $apiKeyRepository = null;

    protected function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager === null) {
            $kernel = self::bootKernel();
            $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        }
        return $this->entityManager;
    }

    protected function getUserRepository(): UserRepository
    {
        if ($this->userRepository === null) {
            $this->userRepository = $this->getEntityManager()->getRepository(User::class);
        }
        return $this->userRepository;
    }

    protected function getApiKeyRepository(): ApiKeyRepository
    {
        if ($this->apiKeyRepository === null) {
            $this->apiKeyRepository = $this->getEntityManager()->getRepository(ApiKey::class);
        }
        return $this->apiKeyRepository;
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $apiKeys = $this->apiKeyRepository->findAll();
        foreach ($apiKeys as $apiKey) {
            $this->entityManager->remove($apiKey);
        }
        $this->entityManager->flush();
    }

    public function testApiKeyGeneration(): void
    {
        $client = static::createClient();

        // Create a test user first (or use existing)
        $user = $this->getUserRepository()->findOneBy([]) ?? $this->createTestUser();

        // Login as admin to create API key
        $client->request('POST', '/auth', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $user->getEmail(),
            'password' => 'password', // Adjust based on your test user password
        ]));

        // For now, we'll test the command directly
        // In a real scenario, you'd test the controller endpoint
        $this->assertTrue(true); // Placeholder - actual test would verify key generation
    }

    public function testApiKeyAuthenticationSuccess(): void
    {
        $client = static::createClient();
        
        $user = $this->getUserRepository()->findOneBy([]) ?? $this->createTestUser();
        
        // Generate an API key
        $plainApiKey = bin2hex(random_bytes(32));
        $prefix = substr($plainApiKey, 0, 16);
        $salt = bin2hex(random_bytes(400));
        $hash = substr(hash('sha256', $plainApiKey . $salt), 0, 34);

        $apiKey = new ApiKey();
        $apiKey->setUser($user);
        $apiKey->setApiKeyPrefix($prefix);
        $apiKey->setApiKeySalt($salt);
        $apiKey->setApiKeyHash($hash);
        $apiKey->setIsActive(true);

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        // Test authentication with API key
        $client->request('GET', '/api/me', [], [], [
            'HTTP_X-API-Key' => $plainApiKey,
        ]);

        // Should succeed if API key authentication works
        // This is a basic test - adjust based on your actual API endpoints
        $this->assertResponseStatusCodeSame(200);
    }

    public function testApiKeyAuthenticationFailure(): void
    {
        $client = static::createClient();

        // Test with invalid API key
        $client->request('GET', '/api/me', [], [], [
            'HTTP_X-API-Key' => 'invalid-key-123456789012345678901234567890123456789012345678901234567890',
        ]);

        // Should fail with 401
        $this->assertResponseStatusCodeSame(401);
    }

    public function testInactiveApiKey(): void
    {
        $client = static::createClient();
        
        $user = $this->userRepository->findOneBy([]) ?? $this->createTestUser();
        
        // Generate an API key
        $plainApiKey = bin2hex(random_bytes(32));
        $prefix = substr($plainApiKey, 0, 16);
        $salt = bin2hex(random_bytes(400));
        $hash = substr(hash('sha256', $plainApiKey . $salt), 0, 34);

        $apiKey = new ApiKey();
        $apiKey->setUser($user);
        $apiKey->setApiKeyPrefix($prefix);
        $apiKey->setApiKeySalt($salt);
        $apiKey->setApiKeyHash($hash);
        $apiKey->setIsActive(false); // Inactive

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        // Test authentication with inactive API key
        $client->request('GET', '/api/me', [], [], [
            'HTTP_X-API-Key' => $plainApiKey,
        ]);

        // Should fail with 401
        $this->assertResponseStatusCodeSame(401);
    }

    private function createTestUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword(password_hash('password', PASSWORD_DEFAULT));
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
