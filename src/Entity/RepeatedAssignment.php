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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
class RepeatedAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['repeatedAssignment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'repeatedAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write'])]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'repeatedAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write'])]
    private ?Seat $seat = null;

    #[ORM\Column]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write'])]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write'])]
    private ?\DateTimeInterface $fromTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write'])]
    private ?\DateTimeInterface $toTime = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['repeatedAssignment:read', 'repeatedAssignment:write'])]
    private ?\DateTimeInterface $untilDate = null;

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
}
