<?php

namespace App\DataFixtures;

use App\Entity\RepeatedAssignment;
use App\Factory\PersonFactory;
use App\Factory\SeatFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RepeatedAssignmentFixtures extends Fixture implements DependentFixtureInterface
{
	private ValidatorInterface $validator;

	public function __construct(ValidatorInterface $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * @throws \Exception
	 */
	public function load(ObjectManager $manager): void
	{
		echo 'Creating not overlapping repeated assignments, this may take a while...' . PHP_EOL;

		for ($i = 0; $i < 200; $i++) {
			$repeatedAssignment = new RepeatedAssignment();

			// Set Person and Seat
			$repeatedAssignment->setPerson(PersonFactory::random()->object());
			$repeatedAssignment->setSeat(SeatFactory::random()->object());

			// Set dayOfWeek
			$repeatedAssignment->setDayOfWeek(random_int(1, 7));

			// Generate random startDate
			$startDate = new \DateTime();
			$startDate->modify(random_int(-10, 10) . ' days');
			$repeatedAssignment->setStartDate($startDate);

			// Generate random untilDate or null
			if (rand(0, 1)) {
				$untilDate = clone $startDate;
				$untilDate->modify('+' . random_int(1, 365) . ' days');
				$repeatedAssignment->setUntilDate($untilDate);
			}

			// Generate random fromTime and toTime
			$fromTime = (new \DateTime())
				->setDate(1970, 1, 1)
				->setTime(random_int(0, 16), 0);
			$repeatedAssignment->setFromTime($fromTime);

			$toTime = clone $fromTime;
			$toTime->modify('+' . random_int(1, 7) . ' hours');
			$repeatedAssignment->setToTime($toTime);

			$violations = $this->validator->validate($repeatedAssignment);
			echo $i . ': ' . count($violations) . PHP_EOL;

			if (count($violations) > 0) {
				--$i;
				continue;
			}

			$manager->persist($repeatedAssignment);
			$manager->flush();
		}
	}

	public function getDependencies(): array
	{
		return [
			PersonFixtures::class,
			SeatFixtures::class,
			AssignmentFixtures::class,
		];
	}
}
