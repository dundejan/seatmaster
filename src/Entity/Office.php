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
use App\Repository\OfficeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OfficeRepository::class)]
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
		'groups' => ['office:read'],
	],
	denormalizationContext: [
		'groups' => ['office:write'],
	],
	paginationItemsPerPage: 10,
)]
class Office
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['office:read', 'office:write', 'assignment:read', 'seat:read'])]
    #[Assert\NotBlank]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private ?string $name = null;
	/**
	 * @var Collection<int, Seat>
	 */
    #[ORM\OneToMany(mappedBy: 'office', targetEntity: Seat::class, orphanRemoval: true)]
    #[Groups(['office:read', 'office:write'])]
    private Collection $seats;

    #[ORM\Column]
    #[Groups(['office:read', 'office:write'])]
    #[Assert\GreaterThanOrEqual(50)]
    private ?int $width = 500;

    #[ORM\Column]
    #[Groups(['office:read', 'office:write'])]
    #[Assert\GreaterThanOrEqual(50)]
    private ?int $height = 500;

    public function __construct()
    {
        $this->seats = new ArrayCollection();
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

    /**
     * @return Collection<int, Seat>
     */
    public function getSeats(): Collection
    {
        return $this->seats;
    }

    public function addSeat(Seat $seat): static
    {
        if (!$this->seats->contains($seat)) {
            $this->seats->add($seat);
            $seat->setOffice($this);
        }

        return $this;
    }

    public function removeSeat(Seat $seat): static
    {
        if ($this->seats->removeElement($seat)) {
            // set the owning side to null (unless already changed)
            if ($seat->getOffice() === $this) {
                $seat->setOffice(null);
            }
        }

        return $this;
    }

	public function __toString(): string
	{
		return (string) $this->name;
	}

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }
}
