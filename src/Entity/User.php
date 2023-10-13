<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
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
		'groups' => ['user:read'],
	],
	denormalizationContext: [
		'groups' => ['user:write'],
	],
	paginationItemsPerPage: 10,
)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email address.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

	/**
	 * @var array<string>
	 */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

	/**
	 * @var Collection<int,ApiToken>
	 */
    #[ORM\OneToMany(mappedBy: 'ownedBy', targetEntity: ApiToken::class)]
    private Collection $apiTokens;

	public function __construct()
    {
        $this->apiTokens = new ArrayCollection();
    }

	public function __toString()
	{
		return $this->email ?: '';
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
	    $roles = $this->roles;

	    // guarantee every user at least has ROLE_USER
	    $roles[] = 'ROLE_USER';

	    return array_unique($roles);
    }

	public function hasRole(string $role) : bool
	{
		return in_array($role, $this->getRoles());
	}

	/**
	 * @param array<string> $roles
	 */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password ?: "";
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): static
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setOwnedBy($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): static
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getOwnedBy() === $this) {
                $apiToken->setOwnedBy(null);
            }
        }

        return $this;
    }

	/**
	 * @return array<int, string|null>
	 */
	public function getValidTokenStrings(): array
	{
		return $this->getApiTokens()
			->filter(fn (ApiToken $token) => $token->isValid())
			->map(fn (ApiToken $token) => $token->getToken())
			->toArray()
			;
	}
}
