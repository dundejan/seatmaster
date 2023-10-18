<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Office;
use App\Entity\Assignment;
use App\Entity\Person;
use App\Entity\Seat;
use App\Repository\AssignmentRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AssignmentCollisionTest extends KernelTestCase
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
	public function testNoCollision(string $fromDate1, string $toDate1, string $fromDate2, string $toDate2): void
	{
		$person = $this->createPerson();
		$seat = $this->createSeat();

		$assignment1 = $this->createAndPersistAssignment($person, $seat, $fromDate1, $toDate1);
		$assignment2 = $this->createAndPersistAssignment($person, $seat, $fromDate2, $toDate2);

		/** @var AssignmentRepository $assignmentRepo */
		$assignmentRepo = $this->entityManager->getRepository(Assignment::class);
		$overlappingAssignments = $assignmentRepo->findOverlappingAssignments($assignment2, 'person');

		$this->assertCount(0, $overlappingAssignments);

		$this->assertAssignmentsExist($assignment1, $assignment2);
	}

	/**
	 * @dataProvider provideCollisionData
	 * @throws Exception
	 */
	public function testCollision(string $fromDate1, string $toDate1, string $fromDate2, string $toDate2): void
	{
		$person = $this->createPerson();
		$seat = $this->createSeat();

		$this->createAndPersistAssignment($person, $seat, $fromDate1, $toDate1);
		$assignment2 = $this->createAssignment($person, $seat, $fromDate2, $toDate2);

		/** @var AssignmentRepository $assignmentRepo */
		$assignmentRepo = $this->entityManager->getRepository(Assignment::class);
		$overlappingAssignments = $assignmentRepo->findOverlappingAssignments($assignment2, 'person');

		$this->assertCount(1, $overlappingAssignments);
	}

	/**
	 * @return array<string[]>
	 */
	public function provideNoCollisionData(): array
	{
		return [
			['2022-10-01 10:00', '2022-10-01 12:00', '2022-10-01 13:00', '2022-10-01 14:00'],
			['2022-10-01 10:00', '2022-10-01 12:00', '2022-10-01 12:00', '2022-10-01 14:00'],
			['2022-10-01 20:00', '2022-10-01 22:00', '2022-10-01 10:00', '2022-10-01 20:00'],
		];
	}

	/**
	 * @return array<string[]>
	 */
	public function provideCollisionData(): array
	{
		return [
			['2022-10-01 10:00', '2022-10-01 12:00', '2022-10-01 11:00', '2022-10-01 13:00'],
			['2022-10-01 10:00', '2022-10-01 12:01', '2022-10-01 12:00', '2022-10-01 13:00'],
			['2022-10-01 10:00', '2022-10-01 12:00', '2022-10-01 09:00', '2022-10-01 11:00'],
			['2022-10-01 10:00', '2022-10-01 12:00', '2022-10-01 10:59', '2022-10-01 11:00'],
			['2022-10-01 10:00', '2022-10-01 10:01', '2022-10-01 10:00', '2022-10-01 10:02'],
			['2022-10-01 10:00', '2022-10-01 10:01', '2022-10-01 00:01', '2022-10-01 23:59'],
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
	private function createAssignment(Person $person, Seat $seat, string $fromDate, string $toDate): Assignment
	{
		$assignment = new Assignment();
		$assignment->setPerson($person)
			->setSeat($seat)
			->setFromDate(new DateTime($fromDate))
			->setToDate(new DateTime($toDate));
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

	private function assertAssignmentsExist(Assignment $assignment1, Assignment $assignment2): void
	{
		$this->assertNotNull($assignment1->getId());
		$this->assertNotNull($assignment2->getId());
	}
}