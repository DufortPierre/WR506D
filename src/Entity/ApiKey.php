<?php

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['api_key:read']],
    denormalizationContext: ['groups' => ['api_key:write']]
)]
class ApiKey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_key:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    #[Groups(['api_key:read', 'api_key:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 34, unique: true)]
    #[Assert\NotBlank(message: "Le hash de la clé API est obligatoire")]
    #[Assert\Length(exactly: 34, exactMessage: "Le hash de la clé API doit contenir exactement 34 caractères")]
    #[Groups(['api_key:read'])]
    private ?string $apiKeyHash = null;

    #[ORM\Column(length: 16)]
    #[Assert\NotBlank(message: "Le préfixe de la clé API est obligatoire")]
    #[Assert\Length(exactly: 16, exactMessage: "Le préfixe de la clé API doit contenir exactement 16 caractères")]
    #[Groups(['api_key:read'])]
    private ?string $apiKeyPrefix = null;

    #[ORM\Column(type: Types::TEXT, length: 800)]
    #[Assert\NotBlank(message: "Le salt de la clé API est obligatoire")]
    #[Assert\Length(max: 800, maxMessage: "Le salt ne peut pas dépasser 800 caractères")]
    private ?string $apiKeySalt = null;

    #[ORM\Column]
    #[Groups(['api_key:read'])]
    private ?\DateTimeImmutable $apiKeyCreatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['api_key:read'])]
    private ?\DateTimeImmutable $apiKeyLastUsedAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Groups(['api_key:read', 'api_key:write'])]
    private bool $isActive = true;

    public function __construct()
    {
        $this->apiKeyCreatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getApiKeyHash(): ?string
    {
        return $this->apiKeyHash;
    }

    public function setApiKeyHash(string $apiKeyHash): static
    {
        $this->apiKeyHash = $apiKeyHash;
        return $this;
    }

    public function getApiKeyPrefix(): ?string
    {
        return $this->apiKeyPrefix;
    }

    public function setApiKeyPrefix(string $apiKeyPrefix): static
    {
        $this->apiKeyPrefix = $apiKeyPrefix;
        return $this;
    }

    public function getApiKeySalt(): ?string
    {
        return $this->apiKeySalt;
    }

    public function setApiKeySalt(string $apiKeySalt): static
    {
        $this->apiKeySalt = $apiKeySalt;
        return $this;
    }

    public function getApiKeyCreatedAt(): ?\DateTimeImmutable
    {
        return $this->apiKeyCreatedAt;
    }

    public function setApiKeyCreatedAt(\DateTimeImmutable $apiKeyCreatedAt): static
    {
        $this->apiKeyCreatedAt = $apiKeyCreatedAt;
        return $this;
    }

    public function getApiKeyLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->apiKeyLastUsedAt;
    }

    public function setApiKeyLastUsedAt(?\DateTimeImmutable $apiKeyLastUsedAt): static
    {
        $this->apiKeyLastUsedAt = $apiKeyLastUsedAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }
}
