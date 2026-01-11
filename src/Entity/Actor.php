<?php

namespace App\Entity;

use App\Repository\ActorRepository;
use App\Entity\MediaObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

// API Platform (REST + GraphQL)
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActorRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['actor:read']],
    denormalizationContext: ['groups' => ['actor:write']],
    graphQlOperations: [
        new Query(),
        new QueryCollection(),
        new Mutation(name: 'create'),
        new Mutation(name: 'update'),
        new Mutation(name: 'delete'),
    ]
)]
class Actor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['actor:read','movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de famille est obligatoire")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit contenir au moins 2 caractères", maxMessage: "Le nom ne peut pas dépasser 255 caractères")]
    #[Groups(['actor:read','actor:write','movie:read'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le prénom doit contenir au moins 2 caractères", maxMessage: "Le prénom ne peut pas dépasser 255 caractères")]
    #[Groups(['actor:read','actor:write','movie:read'])]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['actor:read','actor:write'])]
    private ?\DateTime $dob = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['actor:read','actor:write'])]
    private ?\DateTime $dod = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['actor:read','actor:write'])]
    private ?string $bio = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['actor:read','actor:write','movie:read'])]
    private ?MediaObject $photo = null;

    #[ORM\Column]
    #[Groups(['actor:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\ManyToMany(targetEntity: Movie::class, mappedBy: 'actors')]
    #[Groups(['actor:read','actor:write'])]
    private Collection $movies;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    // -------------------- Getters / Setters --------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getDob(): ?\DateTime
    {
        return $this->dob;
    }

    public function setDob(?\DateTime $dob): static
    {
        $this->dob = $dob;
        return $this;
    }

    public function getDod(): ?\DateTime
    {
        return $this->dod;
    }

    public function setDod(?\DateTime $dod): static
    {
        $this->dod = $dod;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getPhoto(): ?MediaObject
    {
        return $this->photo;
    }

    public function setPhoto(?MediaObject $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
            $movie->addActor($this);
        }
        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        if ($this->movies->removeElement($movie)) {
            $movie->removeActor($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? '')) ?: 'Actor #'.$this->id;
    }
}
