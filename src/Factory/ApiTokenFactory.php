<?php

namespace App\Factory;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ApiToken>
 *
 * @method        ApiToken|Proxy                     create(array|callable $attributes = [])
 * @method static ApiToken|Proxy                     createOne(array $attributes = [])
 * @method static ApiToken|Proxy                     find(object|array|mixed $criteria)
 * @method static ApiToken|Proxy                     findOrCreate(array $attributes)
 * @method static ApiToken|Proxy                     first(string $sortedField = 'id')
 * @method static ApiToken|Proxy                     last(string $sortedField = 'id')
 * @method static ApiToken|Proxy                     random(array $attributes = [])
 * @method static ApiToken|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ApiTokenRepository|RepositoryProxy repository()
 * @method static ApiToken[]|Proxy[]                 all()
 * @method static ApiToken[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ApiToken[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static ApiToken[]|Proxy[]                 findBy(array $attributes)
 * @method static ApiToken[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ApiToken[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<ApiToken> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<ApiToken> createOne(array $attributes = [])
 * @phpstan-method static Proxy<ApiToken> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<ApiToken> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<ApiToken> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<ApiToken> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<ApiToken> random(array $attributes = [])
 * @phpstan-method static Proxy<ApiToken> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<ApiToken> repository()
 */
final class ApiTokenFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'ownedBy' => null, // TODO add App\Entity\User type manually
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ApiToken $apiToken): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ApiToken::class;
    }
}
