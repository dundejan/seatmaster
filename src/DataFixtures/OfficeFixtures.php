<?php

namespace App\DataFixtures;

use App\Factory\OfficeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OfficeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
		OfficeFactory::createMany(6);
    }
}
