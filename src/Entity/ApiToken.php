<?php

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
class ApiToken
{
	/**
	 * @var string Just my prefix smp_ as Seat Master Personal
	 */
	private const PERSONAL_ACCESS_TOKEN_PREFIX = 'smp_';
	public const SCOPE_ADMIN = 'ROLE_ADMIN';
	public const SCOPE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

	public const SCOPES = [
		self::SCOPE_ADMIN => 'Admin access - edit all excluding users',
		self::SCOPE_SUPER_ADMIN => 'Super-admin access - edit all',
	];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ownedBy = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $expiresAt = null;

    #[ORM\Column(length: 68)]
    private string $token;

	/**
	 * @var array<string>
	 */
    #[ORM\Column]
    private array $scopes = [];

	/**
	 * @throws Exception Logic PHP error, never should happen
	 */
	public function __construct(string $tokenType = self::PERSONAL_ACCESS_TOKEN_PREFIX)
	{
		$this->token = $tokenType.bin2hex(random_bytes(32));
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnedBy(): ?User
    {
        return $this->ownedBy;
    }

    public function setOwnedBy(?User $ownedBy): static
    {
        $this->ownedBy = $ownedBy;

        return $this;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

	/**
	 * @return array<string>
	 */
    public function getScopes(): array
    {
        return $this->scopes;
    }

	/**
	 * @param array<string> $scopes
	 */
    public function setScopes(array $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

	public function isValid(): bool
	{
		return $this->expiresAt === null || $this->expiresAt > new DateTimeImmutable();
	}
}
