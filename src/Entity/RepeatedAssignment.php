<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\RepeatedAssignmentRepository;
use App\Validator\IsAvailableAssignment;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: RepeatedAssignmentRepository::class)]
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
         		'groups' => ['repeatedAssignment:read'],
         	],
	denormalizationContext: [
         		'groups' => ['repeatedAssignment:write'],
         	],
	paginationItemsPerPage: 10,
)]
#[IsAvailableAssignment]
class RepeatedAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['repeatedAssignment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'repeatedAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write', 'seat:read'])]
    #[Assert\NotBlank]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'repeatedAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write'])]
    #[Assert\NotBlank]
    private ?Seat $seat = null;

    #[ORM\Column]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write', 'seat:read'])]
    #[Assert\NotBlank]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write', 'seat:read'])]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $fromTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write', 'seat:read'])]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $toTime = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write', 'seat:read'])]
    private ?\DateTimeInterface $untilDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write', 'seat:read'])]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $startDate = null;

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

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getFromTime(): ?\DateTimeInterface
    {
        return $this->fromTime;
    }

    public function setFromTime(\DateTimeInterface $fromTime): static
    {
        $this->fromTime = $fromTime;

        return $this;
    }

    public function getToTime(): ?\DateTimeInterface
    {
        return $this->toTime;
    }

    public function setToTime(?\DateTimeInterface $toTime): static
    {
        $this->toTime = $toTime;

        return $this;
    }

    public function getUntilDate(): ?\DateTimeInterface
    {
        return $this->untilDate;
    }

    public function setUntilDate(?\DateTimeInterface $untilDate): static
    {
        $this->untilDate = $untilDate;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

	/**
	 * @noinspection PhpUnused
	 * @used-by Assert\CallbackValidator
	 */
	#[Assert\Callback]
	public function validateThatAssignmentHasPositiveLength(ExecutionContextInterface $context, mixed $payload): void
	{
		if ($this->getFromTime() >= $this->getToTime()) {
			$context->buildViolation('What are you trying to do? Well, no, the duration of the assignment really can not be negative or zero.')
				->atPath('toTime')
				->addViolation();
		}
	}
}
