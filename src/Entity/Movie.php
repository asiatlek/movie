<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['movie.index', 'category.index', 'movie.info'])]
    private ?int $id = null;

    #[Assert\Length(
        max: 128,
        maxMessage: 'Your name cannot be longer than {{ limit }} characters',
    )]
    #[ORM\Column(length: 128)]
    #[Groups(['movie.index', 'category.index', 'movie.info'])]
    private ?string $name = null;

    #[Assert\Length(
        max: 2048,
        maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
    )]
    #[ORM\Column(length: 2048)]
    #[Groups(['movie.index'])]
    private ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: "/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/",
        message: "Le format de la date de sortie doit être au format ISO 8601."
    )]
    #[ORM\Column(length: 25)]
    #[Groups(['movie.index'])]
    private ?string $releaseAt = null;

    #[Assert\Regex(
        pattern: "/^[1-5]$/",
        message: "La valeur doit être un entier entre 1 et 5."
    )]
    #[ORM\Column(type: "integer", nullable: true)]
    #[Groups(['movie.index'])]
    private ?int $rating = null;



    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: "movies")]
    #[Groups(['movie.index'])]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getReleaseAt(): ?string
    {
        return $this->releaseAt;
    }

    public function setReleaseAt(string $releaseAt): static
    {
        $this->releaseAt = $releaseAt;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
    // Getters and setters for $id, $name, $description, $releaseAt, $rating

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addMovie($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
}
