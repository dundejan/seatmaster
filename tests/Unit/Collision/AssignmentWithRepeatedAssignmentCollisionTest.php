<?php

namespace App\Tests\Unit\Collision;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use App\Repository\AssignmentRepository;
use App\Repository\RepeatedAssignmentRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignmentWithRepeatedAssignmentCollisionTest extends KernelTestCase
{
	private EntityManagerInterface|ObjectManager $entityManager;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$kernel = self::bootKernel();

		/** @var ManagerRegistry|null $doctrine */
		$doctrine = $kernel->getContainer()->get('doctrine');

		if ($doctrine === null) {
			throw new Exception("Doctrine is not available");
		}

		$this->entityManager = $doctrine->getManager();
	}

	/**
	 * @dataProvider provideNoCollisionData
	 * @throws Exception
	 */
	public function testNoCollisionForNotOverlappingAssignmentWithRepeatedAssignment(string $fromDate, string $toDate, string $fromTime, string $toTime, int $dayOfWeek, string $startDate, string|null $untilDate): void
	{
		$person = $this->createPerson();
		$seat = $this->createSeat();

		$assignment = $this->createAndPersistAssignment($person, $seat, $fromDate, $toDate);
		$repeatedAssignment = $this->createAndPersistRepeatedAssignment($person, $seat, $fromTime, $toTime, $dayOfWeek, $startDate, $untilDate);

		/** @var AssignmentRepository $assignmentRepo */
		$assignmentRepo = $this->entityManager->getRepository(Assignment::class);
		$overlappingAssignments = $assignmentRepo->findOverlappingAssignments($repeatedAssignment, 'person');
		$this->assertCount(0, $overlappingAssignments);

		/** @var RepeatedAssignmentRepository $repeatedAssignmentRepo */
		$repeatedAssignmentRepo = $this->entityManager->getRepository(RepeatedAssignment::class);
		$overlappingRepeatedAssignments = $repeatedAssignmentRepo->findOverlappingRepeatedAssignments($assignment, 'person');
		$this->assertCount(0, $overlappingRepeatedAssignments);
	}

	/**
	 * @return array<string[]|int[]|null[]>
	 */
	public function provideNoCollisionData(): array
	{
		return [
			// NO COLLISION DURING DST (Daylight Saving Time)
			['2023-10-01 10:00', '2023-10-01 12:00', '13:00', '14:00', 7, '2023-10-01', null],
			// NO COLLISION BECAUSE I ACCEPT SAME END TIME AND START TIME DURING DST (Daylight Saving Time)
			['2023-10-01 10:00', '2023-10-01 12:00', '12:00', '14:00', 7, '2023-10-01', null],
			// NO COLLISIONS BECAUSE ANOTHER DAY IN WEEK IN REPEATED ASSIGNMENT DURING DST (Daylight Saving Time)
			['2023-10-01 10:00', '2023-10-01 12:00', '10:00', '12:00', 6, '2023-10-01', null],
			['2023-10-01 01:00', '2023-10-01 23:00', '01:00', '23:00', 4, '2023-09-01', null],

			// NO COLLISION NOT DURING DST (Daylight Saving Time)
			['2023-11-01 10:00', '2023-11-01 12:00', '13:00', '14:00', 7, '2023-10-01', null],
			// NO COLLISION BECAUSE I ACCEPT SAME END TIME AND START TIME NOT DURING DST (Daylight Saving Time)
			['2023-11-01 10:00', '2023-11-01 12:00', '12:00', '14:00', 7, '2023-10-01', null],
			// NO COLLISIONS BECAUSE ANOTHER DAY IN WEEK IN REPEATED ASSIGNMENT NOT DURING DST (Daylight Saving Time)
			['2023-11-01 10:00', '2023-11-01 12:00', '10:00', '12:00', 6, '2023-10-01', null],
			['2023-11-01 01:00', '2023-11-01 23:00', '01:00', '23:00', 4, '2023-10-01', null],

			// NO COLLISIONS BECAUSE START DATE IS AFTER END DATE OF ANOTHER DURING DST (Daylight Saving Time)
			['2023-10-01 10:00', '2023-10-01 12:00', '10:00', '12:00', 7, '2023-09-01', '2023-09-30'],
			// NO COLLISIONS BECAUSE START DATE IS AFTER END DATE OF ANOTHER NOT DURING DST (Daylight Saving Time)
			['2023-12-01 10:00', '2023-12-01 12:00', '08:00', '14:00', 7, '2023-10-01', '2023-11-30'],
		];
	}

	/**
	 * @dataProvider provideCollisionData
	 * @throws Exception
	 */
	public function testCollisionForOverlappingAssignmentWithRepeatedAssignment(string $fromDate, string $toDate, string $fromTime, string $toTime, int $dayOfWeek, string $startDate, string|null $untilDate): void
	{
		$person = $this->createPerson();
		$seat = $this->createSeat();

		$assignment = $this->createAndPersistAssignment($person, $seat, $fromDate, $toDate);
		$repeatedAssignment = $this->createAndPersistRepeatedAssignment($person, $seat, $fromTime, $toTime, $dayOfWeek, $startDate, $untilDate);

		/** @var AssignmentRepository $assignmentRepo */
		$assignmentRepo = $this->entityManager->getRepository(Assignment::class);
		$overlappingAssignments = $assignmentRepo->findOverlappingAssignments($repeatedAssignment, 'person');
		$this->assertCount(1, $overlappingAssignments);

		/** @var RepeatedAssignmentRepository $repeatedAssignmentRepo */
		$repeatedAssignmentRepo = $this->entityManager->getRepository(RepeatedAssignment::class);
		$overlappingRepeatedAssignments = $repeatedAssignmentRepo->findOverlappingRepeatedAssignments($assignment, 'person');
		$this->assertCount(1, $overlappingRepeatedAssignments);
	}

	/**
	 * @return array<string[]|int[]|null[]>
	 */
	public function provideCollisionData(): array
	{
		return [
			// COLLISIONS BECAUSE OF TIME DURING DST (Daylight Saving Time)
			['2023-10-01 10:00', '2023-10-01 14:00', '11:00', '16:00', 7, '2023-10-01', null],
			['2023-10-01 10:00', '2023-10-01 12:01', '12:00', '14:00', 7, '2023-10-01', null],
			['2023-10-01 10:00', '2023-10-01 12:00', '08:00', '13:00', 7, '2023-10-01', null],
			['2023-10-01 08:00', '2023-10-01 14:00', '09:00', '09:01', 7, '2023-09-01', null],

			// COLLISIONS BECAUSE OF TIME NOT DURING DST (Daylight Saving Time)
			['2023-11-01 10:00', '2023-11-01 14:00', '11:00', '16:00', 3, '2023-10-01', null],
			['2023-11-01 10:00', '2023-11-01 12:01', '12:00', '14:00', 3, '2023-10-01', null],
			['2023-11-01 10:00', '2023-11-01 12:00', '08:00', '13:00', 3, '2023-10-01', null],
			['2023-11-01 08:00', '2023-11-01 14:00', '09:00', '09:02', 3, '2023-10-01', null],

			// COLLISIONS BECAUSE OF TIME + SAME START DATE AND END DATE
			['2023-09-30 10:00', '2023-09-30 12:00', '10:00', '12:00', 6, '2023-09-01', '2023-09-30'],
			['2023-11-30 10:00', '2023-11-30 12:00', '08:00', '14:00', 4, '2023-10-01', '2023-11-30'],
		];
	}

	// ... Your existing helper methods for creating entities and persisting them.
	private function createPerson(): Person
	{
		$person = new Person();
		$person->setFirstName("John")->setLastName("Doe");
		return $person;
	}

	private function createSeat(): Seat
	{
		$office = new Office();
		$office->setName("Office");
		$this->persistAndFlush([$office]);

		$seat = new Seat();
		$seat->setOffice($office);
		return $seat;
	}

	/**
	 * @throws Exception
	 */
	private function createRepeatedAssignment(
		Person $person, Seat $seat, string $fromTime, string $toTime,
		int $dayOfWeek, string $startDate, string|null $untilDate)
	: RepeatedAssignment
	{
		$repeatedAssignment = new RepeatedAssignment();
		$repeatedAssignment->setPerson($person)
			->setSeat($seat)
			->setFromTime(new DateTime($fromTime))
			->setToTime(new DateTime($toTime))
			->setDayOfWeek($dayOfWeek)
			->setStartDate(new DateTime($startDate))
			->setUntilDate($untilDate ? new DateTime($untilDate) : null);
		return $repeatedAssignment;
	}

	/**
	 * @throws Exception
	 */
	private function createAndPersistRepeatedAssignment(
		Person $person, Seat $seat, string $fromDate, string $toDate,
		int $dayOfWeek, string $startDate, string|null $untilDate)
	: RepeatedAssignment
	{
		$repeatedAssignment = $this->createRepeatedAssignment($person, $seat, $fromDate, $toDate, $dayOfWeek, $startDate, $untilDate);
		$this->persistAndFlush([$person, $seat, $repeatedAssignment]);
		return $repeatedAssignment;
	}

	/**
	 * @param object[] $entities
	 */
	private function persistAndFlush(array $entities): void
	{
		foreach ($entities as $entity) {
			$this->entityManager->persist($entity);
		}
		$this->entityManager->flush();
	}

	/**
	 * @throws Exception
	 */
	private function createAssignment(Person $person, Seat $seat, string $fromDate, string $toDate): Assignment
	{
		$assignment = new Assignment();

		$pragueTimeZone = new DateTimeZone('Europe/Prague');
		$utcTimeZone = new DateTimeZone('UTC');

		$fromDateObj = new DateTime($fromDate, $pragueTimeZone);
		$fromDateObj->setTimezone($utcTimeZone);

		$toDateObj = new DateTime($toDate, $pragueTimeZone);
		$toDateObj->setTimezone($utcTimeZone);

		$assignment->setPerson($person)
			->setSeat($seat)
			->setFromDate($fromDateObj)
			->setToDate($toDateObj);

		return $assignment;
	}

	/**
	 * @throws Exception
	 */
	private function createAndPersistAssignment(Person $person, Seat $seat, string $fromDate, string $toDate): Assignment
	{
		$assignment = $this->createAssignment($person, $seat, $fromDate, $toDate);
		$this->persistAndFlush([$person, $seat, $assignment]);
		return $assignment;
	}
}