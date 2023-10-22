<?php

namespace App\DataFixtures;

use App\Entity\Assignment;
use App\Factory\PersonFactory;
use App\Factory\SeatFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssignmentFixtures extends Fixture implements DependentFixtureInterface
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
		echo 'Creating not overlapping assignments, this may take a while...' . PHP_EOL;

	    for ($i = 0; $i < 300; $i++) {
			$assignment = new Assignment();
			$assignment->setSeat(SeatFactory::random()->object());
			$assignment->setPerson(PersonFactory::random()->object());

		    $baseDate = new \DateTime();
		    $baseDate->modify(random_int(-5, 5) . ' days');

			// Round base date to the nearest hour
			$baseDate->setTime((int)$baseDate->format('H'), 0);

			// Clone the base date to create fromDate
			$fromDate = clone $baseDate;

			// Add random hours and minutes to the base date to create toDate
			$hoursToAdd = random_int(1, 8);
			$minutesToAdd = round(random_int(1, 59) / 10) * 10; // Round to nearest 10

			$toDate = (clone $baseDate)->modify("+{$hoursToAdd} hours {$minutesToAdd} minutes");

			$assignment->setFromDate($fromDate);
			$assignment->setToDate($toDate);

			$violations = $this->validator->validate($assignment);
			//echo $i . ': ' . count($violations) . PHP_EOL;

			if (count($violations) > 0) {
				--$i;
				continue;
			}

		    $manager->persist($assignment);
		    $manager->flush();
	    }
    }

	public function getDependencies(): array
	{
		return [
			PersonFixtures::class,
			SeatFixtures::class,
		];
	}
}
