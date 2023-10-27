<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Office;
use App\Entity\Person;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use App\Repository\RepeatedAssignmentRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class RepeatedAssignmentCollisionTest extends KernelTestCase
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
	public function testNoCollision(
		string $fromTime1, string $toTime1, int $dayOfWeek1, string $startDate1, string|null $untilDate1,
		string $fromTime2, string $toTime2, int $dayOfWeek2, string $startDate2, string|null $untilDate2)
	: void
	{
		$person = $this->createPerson();
		$seat = $this->createSeat();

		$repeatedAssignment1 = $this->createAndPersistRepeatedAssignment($person, $seat, $fromTime1, $toTime1, $dayOfWeek1, $startDate1, $untilDate1);
		$repeatedAssignment2 = $this->createAndPersistRepeatedAssignment($person, $seat, $fromTime2, $toTime2, $dayOfWeek2, $startDate2, $untilDate2);

		/** @var RepeatedAssignmentRepository $repeatedAssignmentRepository */
		$repeatedAssignmentRepository = $this->entityManager->getRepository(RepeatedAssignment::class);

		$overlappingRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($repeatedAssignment2, 'person');
		$this->assertCount(0, $overlappingRepeatedAssignments);
		$overlappingRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($repeatedAssignment2, 'seat');
		$this->assertCount(0, $overlappingRepeatedAssignments);

		$this->assertRepeatedAssignmentsExist($repeatedAssignment1, $repeatedAssignment2);
	}

	/**
	 * @dataProvider provideCollisionData
	 * @throws Exception
	 */
	public function testCollision(
		string $fromTime1, string $toTime1, int $dayOfWeek1, string $startDate1, string|null $untilDate1,
		string $fromTime2, string $toTime2, int $dayOfWeek2, string $startDate2, string|null $untilDate2)
	: void 
	{
		$person = $this->createPerson();
		$seat = $this->createSeat();

		$this->createAndPersistRepeatedAssignment($person, $seat, $fromTime1, $toTime1, $dayOfWeek1, $startDate1, $untilDate1);
		$repeatedAssignment = $this->createRepeatedAssignment($person, $seat, $fromTime2, $toTime2, $dayOfWeek2, $startDate2, $untilDate2);

		/** @var RepeatedAssignmentRepository $repeatedAssignmentRepository */
		$repeatedAssignmentRepository = $this->entityManager->getRepository(RepeatedAssignment::class);

		$overlappingRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($repeatedAssignment, 'person');
		$this->assertCount(1, $overlappingRepeatedAssignments);

		$overlappingRepeatedAssignments = $repeatedAssignmentRepository->findOverlappingRepeatedAssignments($repeatedAssignment, 'seat');
		$this->assertCount(1, $overlappingRepeatedAssignments);
	}

	/**
	 * @return array<string[]|int[]|null[]>
	 */
	public function provideNoCollisionData(): array
	{
		return [
			['10:00', '12:00', 1, '2023-10-01', null, '13:00', '14:00', 1, '2023-10-01', null],
			['10:00', '12:00', 5, '2023-10-01', null, '12:00', '14:00', 5, '2023-10-01', null],
			['20:00', '22:00', 6, '2023-10-01', null, '10:00', '20:00', 6, '2023-10-01', null],
			['10:00', '12:00', 1, '2023-10-01', null, '10:00', '12:00', 2, '2023-10-01', null],
			['10:00', '12:00', 1, '2023-10-01', '2023-10-07', '10:00', '12:00', 1, '2023-10-08', null],
			['10:00', '11:00', 5, '2023-10-08', null, '10:00', '12:00', 5, '2023-01-01', '2023-10-07'],
		];
	}

	/**
	 * @return array<string[]|int[]|null[]>
	 */
	public function provideCollisionData(): array
	{
		return [
			['10:00', '12:00', 1, '2023-10-01', null, '11:00', '14:00', 1, '2023-10-01', null],
			['10:00', '13:00', 5, '2023-10-01', null, '12:59', '14:00', 5, '2023-10-01', null],
			['20:00', '22:00', 6, '2023-10-01', null, '10:00', '23:00', 6, '2023-10-01', null],
			['10:00', '12:00', 1, '2023-10-01', '2023-10-07', '10:00', '12:00', 1, '2023-10-07', null],
			['10:00', '11:00', 5, '2023-10-07', null, '10:00', '12:00', 5, '2023-01-01', '2023-10-07'],
		];
	}

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

	private function assertRepeatedAssignmentsExist(RepeatedAssignment $repeatedAssignment1, RepeatedAssignment $repeatedAssignment2): void
	{
		$this->assertNotNull($repeatedAssignment1->getId());
		$this->assertNotNull($repeatedAssignment2->getId());
	}
}