<?php

namespace App\DataFixtures;

use App\Entity\Office;
use App\Entity\Seat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeatFixtures extends Fixture implements DependentFixtureInterface
{
	public const SEAT_1_REFERENCE = 'seat-1';
	public const SEAT_2_REFERENCE = 'seat-2';
	public const SEAT_3_REFERENCE = 'seat-3';
	public const SEAT_4_REFERENCE = 'seat-4';
	public const SEAT_5_REFERENCE = 'seat-5';
	public const SEAT_6_REFERENCE = 'seat-6';

    public function load(ObjectManager $manager): void
    {
        $seat1 = new Seat();
	    /** @var Office $office */
	    $office = $this->getReference(OfficeFixtures::OFFICE_1_REFERENCE);
		$seat1->setOffice($office);

	    $seat2 = new Seat();
	    $seat2->setOffice($office);

	    $seat3 = new Seat();
	    /** @var Office $office */
	    $office = $this->getReference(OfficeFixtures::OFFICE_2_REFERENCE);
	    $seat3->setOffice($office);

	    $seat4 = new Seat();
	    /** @var Office $office */
	    $office = $this->getReference(OfficeFixtures::OFFICE_3_REFERENCE);
	    $seat4->setOffice($office);

	    $seat5 = new Seat();
	    /** @var Office $office */
	    $office = $this->getReference(OfficeFixtures::OFFICE_4_REFERENCE);
	    $seat5->setOffice($office);

	    $seat6 = new Seat();
	    $seat6->setOffice($office);

        $manager->persist($seat1);
	    $manager->persist($seat2);
	    $manager->persist($seat3);
	    $manager->persist($seat4);
	    $manager->persist($seat5);
	    $manager->persist($seat6);

        $manager->flush();

	    $this->addReference(self::SEAT_1_REFERENCE, $seat1);
	    $this->addReference(self::SEAT_2_REFERENCE, $seat2);
	    $this->addReference(self::SEAT_3_REFERENCE, $seat3);
	    $this->addReference(self::SEAT_4_REFERENCE, $seat4);
	    $this->addReference(self::SEAT_5_REFERENCE, $seat5);
	    $this->addReference(self::SEAT_6_REFERENCE, $seat6);
    }

	public function getDependencies(): array
	{
		return [
			OfficeFixtures::class,
		];
	}
}
