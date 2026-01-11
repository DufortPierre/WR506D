<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use App\Entity\MediaObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

// API Platform
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['movie:read']],
    denormalizationContext: ['groups' => ['movie:write']],
    graphQlOperations: [
        new Query(),
        new QueryCollection(),
        new Mutation(name: 'create'),
        new Mutation(name: 'update'),
        new Mutation(name: 'delete'),
    ]
)]
#[
    // Filtre booléen sur le bon champ
    ApiFilter(BooleanFilter::class, properties: ['online']),
    // Recherche : id exact, name partiel
    ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'name' => 'partial']),
    // Plage sur la durée
    ApiFilter(RangeFilter::class, properties: ['duration']),
    // Tri (remplace 'title' par 'name' qui existe vraiment)
    ApiFilter(OrderFilter::class, properties: ['releaseDate', 'name'], arguments: ['orderParameterName' => 'order'])
]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['movie:read','actor:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du film est obligatoire")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "Le nom doit contenir au moins 1 caractère",
        maxMessage: "Le nom ne peut pas dépasser 255 caractères"
    )]
    #[Groups(['movie:read','movie:write','actor:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['movie:read','movie:write'])]
    private bool $online = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['movie:read','movie:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['movie:read','movie:write'])]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['movie:read','movie:write'])]
    private ?\DateTime $releaseDate = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['movie:read','movie:write'])]
    private ?MediaObject $image = null;

           #[ORM\Column]
           #[Groups(['movie:read'])]
           #[SerializedName('created_at')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'integer', message: "Le nombre d'entrées doit être un entier")]
    #[Assert\GreaterThanOrEqual(value: 0, message: "Le nombre d'entrées doit être positif ou nul")]
    #[Groups(['movie:read','movie:write'])]
    private ?int $nbEntries = null;

    #[ORM\ManyToOne(targetEntity: Director::class, inversedBy: 'movies')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull(message: "Le réalisateur est obligatoire", groups: ['movie_creation'])]
    #[Groups(['movie:read','movie:write'])]
    private ?Director $director = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL doit être une URL valide")]
    #[Assert\Length(max: 255, maxMessage: "L'URL ne peut pas dépasser 255 caractères")]
    #[Groups(['movie:read','movie:write'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\Type(type: 'float', message: "Le budget doit être un nombre décimal")]
    #[Assert\GreaterThanOrEqual(value: 0, message: "Le budget doit être positif ou nul")]
    #[Groups(['movie:read','movie:write'])]
    private ?float $budget = null;

           /**
            * @var Collection<int, Actor>
            * Owning side (inversedBy = "movies" dans Actor)
            */
           #[ORM\ManyToMany(targetEntity: Actor::class, inversedBy: 'movies')]
           #[Groups(['movie:read','movie:write'])]
           #[MaxDepth(2)]
    private Collection $actors;

           /**
            * @var Collection<int, Category>
            * Inverse side (mappedBy = "movies" dans Category)
            */
           #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'movies')]
           #[Groups(['movie:read','movie:write'])]
           #[MaxDepth(2)]
    private Collection $categories;

    public function __construct()
    {
        $this->actors = new ArrayCollection();
        $this->categories = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }
    public function setOnline(bool $online): static
    {
        $this->online = $online;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }
    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }
    public function setReleaseDate(?\DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    public function getImage(): ?MediaObject
    {
        return $this->image;
    }
    public function setImage(?MediaObject $image): static
    {
        $this->image = $image;
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

    public function getNbEntries(): ?int
    {
        return $this->nbEntries;
    }
    public function setNbEntries(?int $nbEntries): static
    {
        $this->nbEntries = $nbEntries;
        return $this;
    }

    public function getDirector(): ?Director
    {
        return $this->director;
    }
    public function setDirector(?Director $director): static
    {
        $this->director = $director;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }
    public function setBudget(?float $budget): static
    {
        $this->budget = $budget;
        return $this;
    }

    /** @return Collection<int, Actor> */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
            $actor->addMovie($this); // synchro inverse
        }
        return $this;
    }

    public function removeActor(Actor $actor): static
    {
        if ($this->actors->removeElement($actor)) {
            $actor->removeMovie($this); // synchro inverse
        }
        return $this;
    }

    /** @return Collection<int, Category> */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addMovie($this); // owning side = Category
        }
        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeMovie($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? ('Movie #'.$this->id);
    }
}
