<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ApiResource(
	operations: [
                  		new Get(),
                  		new GetCollection(),
                  		new Post(),
                  		new Put(),
                  		new Patch(),
                  		new Delete(),
                  	],
	formats: [
                  		'jsonld',
                  		'json',
                  		'html',
                  		'csv' => 'text/csv',
                  	],
	normalizationContext: [
                  		'groups' => ['person:read'],
                  	],
	denormalizationContext: [
                  		'groups' => ['person:write'],
                  	],
	paginationItemsPerPage: 10,
)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['person:read', 'person:write', 'assignment:read'])]
    #[Assert\NotBlank]
    private ?string $lastName = null;
	/**
	 * @var Collection<int, Assignment>
	 */
    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Assignment::class, orphanRemoval: true)]
    #[Groups(['person:read', 'person:write'])]
    private Collection $assignments;

    #[ORM\Column(nullable: true)]
    private ?int $idExternal = null;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
    }

	public function __toString(): string
	{
		return $this->getFullName();
	}

	public function getFullName(): string
	{
		return $this->firstName . ' ' . $this->lastName;
	}

    public function getId(): ?int
    {
        return $this->id;
    }

	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	public function setFirstName(string $firstName): static
	{
		$this->firstName = $firstName;

		return $this;
	}

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(Assignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setPerson($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getPerson() === $this) {
                $assignment->setPerson(null);
            }
        }

        return $this;
    }

    public function getIdExternal(): ?int
    {
        return $this->idExternal;
    }

    public function setIdExternal(?int $idExternal): static
    {
        $this->idExternal = $idExternal;

        return $this;
    }
}
