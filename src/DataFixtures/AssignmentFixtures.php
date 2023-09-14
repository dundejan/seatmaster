<?php

namespace App\DataFixtures;

use App\Entity\Assignment;
use App\Entity\Person;
use App\Entity\Seat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AssignmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $assignment1 = new Assignment();
	    /** @var Person $person */
		$person = $this->getReference(PersonFixtures::PERSON_1_REFERENCE);
	    /** @var Seat $seat */
		$seat = $this->getReference(SeatFixtures::SEAT_1_REFERENCE);
		$assignment1->setPerson($person)->setSeat($seat)->setFromDate(new \DateTime('today 10:00'))->setToDate(new \DateTime('today 12:30'));

	    $assignment2 = new Assignment();
	    /** @var Person $person */
	    $person = $this->getReference(PersonFixtures::PERSON_2_REFERENCE);
	    /** @var Seat $seat */
	    $seat = $this->getReference(SeatFixtures::SEAT_4_REFERENCE);
	    $assignment2->setPerson($person)->setSeat($seat)->setFromDate(new \DateTime('tomorrow 8:00'))->setToDate(new \DateTime('tomorrow 16:00'));

	    $assignment3 = new Assignment();
	    /** @var Person $person */
	    $person = $this->getReference(PersonFixtures::PERSON_3_REFERENCE);
	    /** @var Seat $seat */
	    $seat = $this->getReference(SeatFixtures::SEAT_2_REFERENCE);
	    $assignment3->setPerson($person)->setSeat($seat)->setFromDate(new \DateTime('tomorrow 8:00'))->setToDate(new \DateTime('tomorrow 12:00'));

	    $assignment4 = new Assignment();
	    /** @var Person $person */
	    $person = $this->getReference(PersonFixtures::PERSON_4_REFERENCE);
	    /** @var Seat $seat */
	    $seat = $this->getReference(SeatFixtures::SEAT_3_REFERENCE);
	    $assignment4->setPerson($person)->setSeat($seat)->setFromDate(new \DateTime('today 8:00'))->setToDate(new \DateTime('today 12:30 '));

	    $assignment5 = new Assignment();
	    /** @var Person $person */
	    $person = $this->getReference(PersonFixtures::PERSON_4_REFERENCE);
	    /** @var Seat $seat */
	    $seat = $this->getReference(SeatFixtures::SEAT_1_REFERENCE);
	    $assignment5->setPerson($person)->setSeat($seat)->setFromDate(new \DateTime('today 12:30'))->setToDate(new \DateTime('today 16:00'));

        $manager->persist($assignment1);
	    $manager->persist($assignment2);
	    $manager->persist($assignment3);
	    $manager->persist($assignment4);
	    $manager->persist($assignment5);

        $manager->flush();
    }

	public function getDependencies(): array
	{
		return [
			PersonFixtures::class,
			SeatFixtures::class,
		];
	}
}
