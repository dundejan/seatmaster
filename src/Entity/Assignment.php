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
use App\Repository\AssignmentRepository;
use App\Validator\IsAvailableAssignment;
use App\Validator\IsFutureAssignment;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
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
		'groups' => ['assignment:read'],
	],
	denormalizationContext: [
		'groups' => ['assignment:write'],
	],
	paginationItemsPerPage: 10,
)]
#[ApiFilter(SearchFilter::class, properties: [
	'seat.office.name' => 'partial',
	'person.name' => 'partial',
	'seat.id' => 'exact',
])]
#[IsAvailableAssignment]
class Assignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['assignment:read', 'assignment:write'])]
    #[Assert\NotBlank]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['assignment:read', 'assignment:write'])]
    #[Assert\NotBlank]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?Seat $seat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['assignment:read', 'assignment:write', 'seat:read'])]
    #[Assert\NotBlank]
    #[IsFutureAssignment]
    private ?\DateTimeInterface $fromDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['assignment:read', 'assignment:write', 'seat:read'])]
    #[Assert\NotBlank]
    #[IsFutureAssignment]
    private ?\DateTimeInterface $toDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getSeat(): ?Seat
    {
        return $this->seat;
    }

    public function setSeat(?Seat $seat): static
    {
        $this->seat = $seat;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): static
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(\DateTimeInterface $toDate): static
    {
        $this->toDate = $toDate;

        return $this;
    }

	#[Assert\Callback]
	public function validateThatAssignmentHasPositiveLength(ExecutionContextInterface $context, mixed $payload): void
	{
		if ($this->getFromDate() >= $this->getToDate()) {
			$context->buildViolation('What are you trying to do? Well, no, the duration of the assignment really can not be negative or zero.')
				->atPath('toDate')
				->addViolation();
		}
	}

	public function validateThatSeatIsAvailable(ExecutionContextInterface $context): void
	{
		//TODO

	}

	public function validateThatPersonIsAvailable(ExecutionContextInterface $context): void
	{
		//TODO
	}
}
