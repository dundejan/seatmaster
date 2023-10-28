<?php

namespace App\Tests;

use App\Entity\ApiToken;
use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\RepeatedAssignment;
use App\Entity\Seat;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntityRelationshipTest extends KernelTestCase
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

	public function testApiTokenUserRelation(): void
	{
		$user = new User();
		$user->setEmail('user@example.com')
			->setPassword('password');
		$apiToken = new ApiToken();

		$apiToken->setOwnedBy($user);

		$this->entityManager->persist($user);
		$this->entityManager->persist($apiToken);
		$this->entityManager->flush();

		$this->assertSame($user, $apiToken->getOwnedBy());
	}

	public function testAssignmentRelations(): void
	{
		$person = new Person();
		$person->setFirstName('John')
			->setLastName('Doe');
		$office = new Office();
		$office->setName('office');

		$seat = new Seat();
		$seat->setOffice($office);
		$assignment = new Assignment();
		$assignment->setFromDate(new DateTime())
			->setToDate(new DateTime('tomorrow'));

		$assignment->setPerson($person);
		$assignment->setSeat($seat);

		$this->entityManager->persist($office);
		$this->entityManager->persist($person);
		$this->entityManager->persist($seat);
		$this->entityManager->persist($assignment);
		$this->entityManager->flush();

		$this->assertSame($person, $assignment->getPerson());
		$this->assertSame($seat, $assignment->getSeat());
	}

	public function testOfficeSeatRelation(): void
	{
		$office = new Office();
		$office->setName('office');
		$seat = new Seat();

		$office->addSeat($seat);

		$this->entityManager->persist($office);
		$this->entityManager->persist($seat);
		$this->entityManager->flush();

		$this->assertIsNumeric($seat->getId());

		$office->removeSeat($seat);
		$this->entityManager->flush();

		$this->assertEmpty($office->getSeats());

		// orphan removal
		$this->assertNull($seat->getId());
	}

	public function testPersonAssignmentRelation(): void
	{
		$person = new Person();
		$person->setFirstName('John')
			->setLastName('Doe');
		$office = new Office();
		$office->setName('office');

		$seat = new Seat();
		$seat->setOffice($office);
		$assignment = new Assignment();
		$assignment->setFromDate(new DateTime())
			->setToDate(new DateTime('tomorrow'));

		$assignment->setSeat($seat);
		$person->addAssignment($assignment);

		$this->entityManager->persist($office);
		$this->entityManager->persist($seat);
		$this->entityManager->persist($person);
		$this->entityManager->persist($assignment);
		$this->entityManager->flush();

		// Validate mappedBy, expecting the inverse relation to be set
		$this->assertSame($person, $assignment->getPerson());
	}

	public function testPersonRepeatedAssignmentRelation(): void
	{
		$person = new Person();
		$person->setFirstName('Jane')
			->setLastName('Doe');

		$seat = new Seat();
		$office = new Office();
		$office->setName('Main Office');
		$seat->setOffice($office);

		$repeatedAssignment = new RepeatedAssignment();
		$repeatedAssignment->setStartDate(new DateTime())
			->setUntilDate(new DateTime('tomorrow'))
			->setDayOfWeek(1)
			->setFromTime(new DateTime())
			->setToTime(new DateTime('+2 hours'));

		$repeatedAssignment->setSeat($seat);
		$person->addRepeatedAssignment($repeatedAssignment);

		$this->entityManager->persist($office);
		$this->entityManager->persist($seat);
		$this->entityManager->persist($person);
		$this->entityManager->persist($repeatedAssignment);
		$this->entityManager->flush();

		// Validate mappedBy, expecting the inverse relation to be set
		$this->assertSame($person, $repeatedAssignment->getPerson());
	}

	public function testSeatRepeatedAssignmentRelation(): void
	{
		$person = new Person();
		$person->setFirstName('Jane')
			->setLastName('Doe');

		$seat = new Seat();
		$office = new Office();
		$office->setName('Second Office');
		$seat->setOffice($office);

		$repeatedAssignment = new RepeatedAssignment();
		$repeatedAssignment->setStartDate(new DateTime())
			->setUntilDate(new DateTime('tomorrow'))
			->setDayOfWeek(1)
			->setFromTime(new DateTime())
			->setToTime(new DateTime('+2 hours'));

		$repeatedAssignment->setPerson($person);

		$seat->addRepeatedAssignment($repeatedAssignment);

		$this->entityManager->persist($person);
		$this->entityManager->persist($office);
		$this->entityManager->persist($seat);
		$this->entityManager->persist($repeatedAssignment);
		$this->entityManager->flush();

		$seat->removeRepeatedAssignment($repeatedAssignment);
		$this->entityManager->flush();

		// orphan removal
		$this->assertNull($repeatedAssignment->getId());
	}
}
