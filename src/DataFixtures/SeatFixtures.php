<?php

namespace App\DataFixtures;

use App\Entity\Office;
use App\Entity\Seat;
use App\Factory\OfficeFactory;
use App\Factory\SeatFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeatFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
		SeatFactory::createMany(60, function() {
			return [
				'office' => OfficeFactory::random()
			];
		});
    }

	public function getDependencies(): array
	{
		return [
			OfficeFixtures::class,
		];
	}
}
