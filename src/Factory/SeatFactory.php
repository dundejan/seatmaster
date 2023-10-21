<?php

namespace App\Factory;

use App\Entity\Seat;
use App\Repository\SeatRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Seat>
 *
 * @method        Seat|Proxy                     create(array|callable $attributes = [])
 * @method static Seat|Proxy                     createOne(array $attributes = [])
 * @method static Seat|Proxy                     find(object|array|mixed $criteria)
 * @method static Seat|Proxy                     findOrCreate(array $attributes)
 * @method static Seat|Proxy                     first(string $sortedField = 'id')
 * @method static Seat|Proxy                     last(string $sortedField = 'id')
 * @method static Seat|Proxy                     random(array $attributes = [])
 * @method static Seat|Proxy                     randomOrCreate(array $attributes = [])
 * @method static SeatRepository|RepositoryProxy repository()
 * @method static Seat[]|Proxy[]                 all()
 * @method static Seat[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Seat[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Seat[]|Proxy[]                 findBy(array $attributes)
 * @method static Seat[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Seat[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<Seat> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<Seat> createOne(array $attributes = [])
 * @phpstan-method static Proxy<Seat> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<Seat> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<Seat> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<Seat> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<Seat> random(array $attributes = [])
 * @phpstan-method static Proxy<Seat> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<Seat> repository()
 * @phpstan-method static list<Proxy<Seat>> all()
 */
final class SeatFactory extends ModelFactory
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
     */
    protected function getDefaults(): array
    {
        return [
            'office' => OfficeFactory::new(),
	        'coordX' => round(self::faker()->numberBetween(0,500) / 100) * 100,
	        'coordY' => round(self::faker()->numberBetween(0,500) / 200) * 200,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Seat $seat): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Seat::class;
    }
}
