<?php

namespace App\Factory;

use App\Entity\Office;
use App\Repository\OfficeRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Office>
 *
 * @method        Office|Proxy                     create(array|callable $attributes = [])
 * @method static Office|Proxy                     createOne(array $attributes = [])
 * @method static Office|Proxy                     find(object|array|mixed $criteria)
 * @method static Office|Proxy                     findOrCreate(array $attributes)
 * @method static Office|Proxy                     first(string $sortedField = 'id')
 * @method static Office|Proxy                     last(string $sortedField = 'id')
 * @method static Office|Proxy                     random(array $attributes = [])
 * @method static Office|Proxy                     randomOrCreate(array $attributes = [])
 * @method static OfficeRepository|RepositoryProxy repository()
 * @method static Office[]|Proxy[]                 all()
 * @method static Office[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Office[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Office[]|Proxy[]                 findBy(array $attributes)
 * @method static Office[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Office[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<Office> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<Office> createOne(array $attributes = [])
 * @phpstan-method static Proxy<Office> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<Office> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<Office> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<Office> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<Office> random(array $attributes = [])
 * @phpstan-method static Proxy<Office> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<Office> repository()
 * @phpstan-method static list<Proxy<Office>> all()
 */
final class OfficeFactory extends ModelFactory
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

	private function generateOfficeName(): string
	{
		$animalOfficeNames = [
			'Panthra',
			'Sharkus',
			'Zebrox',
			'Turtlo',
			'Dolphix',
			'Snayke',
			'Eaglix',
			'Pengwin',
			'Rabbot',
			'Giruff',
			'Flaminglo',
			'Cheetoz'
		];

		$core = ['Venture', 'Business', 'Solutions', 'Workspace', 'Management'];
		$suffixes = ['A', 'B', 'C'];

		return
			self::faker()->randomElement($animalOfficeNames) . ' ' .
			self::faker()->randomElement($core) . ' ' .
			self::faker()->randomElement($suffixes);
	}

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
	        'name' => self::generateOfficeName(),
            'height' => self::faker()->numberBetween(500,800),
            'width' => self::faker()->numberBetween(500,800),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Office $office): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Office::class;
    }
}
