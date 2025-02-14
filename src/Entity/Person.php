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
		new Post(security: 'is_granted("ROLE_ADMIN")'),
		new Put(security: 'is_granted("ROLE_ADMIN")'),
		new Patch(security: 'is_granted("ROLE_ADMIN")'),
		new Delete(security: 'is_granted("ROLE_ADMIN")'),
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
    #[Groups(['person:read', 'person:write', 'assignment:read', 'repeatedAssignment:read'])]
    private ?int $id = null;

	#[ORM\Column(length: 255)]
	#[Groups(['person:read', 'person:write', 'assignment:read', 'repeatedAssignment:read'])]
	#[Assert\NotBlank]
	private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['person:read', 'person:write', 'assignment:read', 'repeatedAssignment:read'])]
    #[Assert\NotBlank]
    private ?string $lastName = null;

	/**
	 * @var Collection<int, Assignment>
	 */
    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Assignment::class, orphanRemoval: true)]
    #[Groups(['person:read', 'person:write'])]
    private Collection $assignments;

    #[ORM\Column(nullable: true)]
    private ?string $idExternal = null;

	/**
	 * @var Collection<int, RepeatedAssignment>
	 */
    #[ORM\OneToMany(mappedBy: 'person', targetEntity: RepeatedAssignment::class, orphanRemoval: true)]
    private Collection $repeatedAssignments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jobTitle = null;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->repeatedAssignments = new ArrayCollection();
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

    public function getIdExternal(): ?string
    {
        return $this->idExternal;
    }

    public function setIdExternal(?string $idExternal): static
    {
        $this->idExternal = $idExternal;

        return $this;
    }

    /**
     * @return Collection<int, RepeatedAssignment>
     */
    public function getRepeatedAssignments(): Collection
    {
        return $this->repeatedAssignments;
    }

    public function addRepeatedAssignment(RepeatedAssignment $repeatedAssignment): static
    {
        if (!$this->repeatedAssignments->contains($repeatedAssignment)) {
            $this->repeatedAssignments->add($repeatedAssignment);
            $repeatedAssignment->setPerson($this);
        }

        return $this;
    }

    public function removeRepeatedAssignment(RepeatedAssignment $repeatedAssignment): static
    {
        if ($this->repeatedAssignments->removeElement($repeatedAssignment)) {
            // set the owning side to null (unless already changed)
            if ($repeatedAssignment->getPerson() === $this) {
                $repeatedAssignment->setPerson(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }
}
