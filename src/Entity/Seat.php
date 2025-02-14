<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\SeatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SeatRepository::class)]
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
         		'groups' => ['seat:read'],
         	],
	denormalizationContext: [
         		'groups' => ['seat:write'],
         	],
	paginationItemsPerPage: 10,
)]
#[ApiFilter(SearchFilter::class, properties: [
	'office.name' => 'partial',
	'office.id' => 'exact',
])]
class Seat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['seat:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'seats')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['seat:read', 'seat:write', 'assignment:read'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private ?Office $office = null;

	/**
	 * @var Collection<int, Assignment>
	 */
    #[ORM\OneToMany(mappedBy: 'seat', targetEntity: Assignment::class, orphanRemoval: true)]
    #[Groups(['seat:read', 'seat:write'])]
    private Collection $assignments;

    #[ORM\Column(nullable: true)]
    #[Groups(['seat:read', 'seat:write'])]
    private ?int $coordX = 0;

    #[ORM\Column(nullable: true)]
    #[Groups(['seat:read', 'seat:write'])]
    private ?int $coordY = 0;

	/**
	 * @var Collection<int, RepeatedAssignment>
	 */
    #[ORM\OneToMany(mappedBy: 'seat', targetEntity: RepeatedAssignment::class, orphanRemoval: true)]
    #[Groups(['seat:read', 'seat:write'])]
    private Collection $repeatedAssignments;

    #[ORM\Column]
    #[Groups(['seat:read', 'seat:write'])]
    private ?int $rotation = 0;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->repeatedAssignments = new ArrayCollection();
    }

	public function __toString(): string
	{
		return (string) $this->id . ' (' . $this->office . ')';
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOffice(): ?Office
    {
        return $this->office;
    }

    public function setOffice(?Office $office): static
    {
        $this->office = $office;

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
            $assignment->setSeat($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getSeat() === $this) {
                $assignment->setSeat(null);
            }
        }

        return $this;
    }

    public function getCoordX(): ?int
    {
        return $this->coordX;
    }

    public function setCoordX(?int $coordX): static
    {
        $this->coordX = $coordX;

        return $this;
    }

    public function getCoordY(): ?int
    {
        return $this->coordY;
    }

    public function setCoordY(?int $coordY): static
    {
        $this->coordY = $coordY;

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
            $repeatedAssignment->setSeat($this);
        }

        return $this;
    }

    public function removeRepeatedAssignment(RepeatedAssignment $repeatedAssignment): static
    {
        if ($this->repeatedAssignments->removeElement($repeatedAssignment)) {
            // set the owning side to null (unless already changed)
            if ($repeatedAssignment->getSeat() === $this) {
                $repeatedAssignment->setSeat(null);
            }
        }

        return $this;
    }

    public function getRotation(): ?int
    {
        return $this->rotation;
    }

    public function setRotation(int $rotation): static
    {
        $this->rotation = $rotation;

        return $this;
    }
}
