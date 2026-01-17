<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['category:read']]
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['category:list']]
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['category:read']]
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['category:read']]
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    denormalizationContext: ['groups' => ['category:write']],
    graphQlOperations: [
        new Query(),
        new QueryCollection(),
        new Mutation(name: 'create'),
        new Mutation(name: 'update'),
        new Mutation(name: 'delete'),
    ]
)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read', 'category:list', 'movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la catégorie est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir au moins 2 caractères",
        maxMessage: "Le nom ne peut pas dépasser 255 caractères"
    )]
    #[Groups(['category:read', 'category:list', 'category:write', 'movie:read'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['category:read'])]
    #[SerializedName('created_at')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\ManyToMany(targetEntity: Movie::class, inversedBy: 'categories')]
    #[Groups(['category:read', 'category:write'])]
    #[MaxDepth(2)]
    private Collection $movies;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $movie->addCategory($this);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        if ($this->movies->removeElement($movie)) {
            $movie->removeCategory($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? ('Category #'.$this->id);
    }
}
