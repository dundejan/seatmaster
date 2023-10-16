<?php
//
//namespace App\Tests;
//
//use App\Entity\Office;
//use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
//use Doctrine\ORM\EntityManagerInterface;
//use App\Entity\Assignment;
//use App\Entity\RepeatedAssignment;
//use App\Entity\Person;
//use App\Entity\Seat;
//
//class AssignmentCollisionTest extends KernelTestCase
//{
//	private $entityManager;
//	private $person;
//	private $seat;
//	private $office;
//
//	public function setUp(): void
//	{
//		$kernel = self::bootKernel();
//		$this->entityManager = $kernel->getContainer()
//			->get('doctrine')
//			->getManager();
//
//		$this->person = new Person();
//		$this->person->setFirstName('John');
//		$this->person->setLastName('Doe');
//		$this->entityManager->persist($this->person);
//
//		$this->office = new Office();
//		$this->office->setName('Office');
//		$this->entityManager->persist($this->office);
//
//		$this->seat = new Seat();
//		$this->seat->setOffice($this->office);
//		$this->entityManager->persist($this->seat);
//
//		$this->entityManager->flush();
//	}
//
////	public function testNoCollision(): void
////	{
////		$assignment = new Assignment();
////		$assignment->setPerson($this->person);
////		$assignment->setSeat($this->seat);
////		$assignment->setFromDate(new \DateTime('2022-10-01 10:00'));
////		$assignment->setToDate(new \DateTime('2022-10-01 12:00'));
////
////		$this->entityManager->persist($assignment);
////		$this->entityManager->flush();
////
////		$this->assertNotNull($assignment->getId(), 'The assignment ID should not be null');
////
////		// Optional: Fetch it back to make sure it saved correctly.
////		$savedAssignment = $this->entityManager->getRepository(Assignment::class)->find($assignment->getId());
////		$this->assertNotNull($savedAssignment, 'The saved assignment should be retrievable');
////		$this->assertEquals($this->person->getId(), $savedAssignment->getPerson()->getId());
////		$this->assertEquals($this->seat->getId(), $savedAssignment->getSeat()->getId());
////
////		// Validate and save the assignment
////		// Assertions here for successful save
////
////		$anotherAssignment = new Assignment();
////		$anotherAssignment->setPerson($this->person);
////		$anotherAssignment->setSeat($this->seat);
////		$anotherAssignment->setFromDate(new \DateTime('2022-10-01 13:00'));
////		$anotherAssignment->setToDate(new \DateTime('2022-10-01 14:00'));
////
////		// Validate and save the anotherAssignment
////		// Assertions here for successful save
////	}
////
////	public function testCollision(): void
////	{
////		$person = // get or create a Person entity
////		$seat = // get or create a Seat entity
////
////		$assignment = new Assignment();
////		$assignment->setPerson($person);
////		$assignment->setSeat($seat);
////		$assignment->setFromDate(new \DateTime('2022-10-01 10:00'));
////		$assignment->setToDate(new \DateTime('2022-10-01 12:00'));
////
////		// Validate and save the assignment
////		// Assertions here for successful save
////
////		$collisionAssignment = new Assignment();
////		$collisionAssignment->setPerson($person);
////		$collisionAssignment->setSeat($seat);
////		$collisionAssignment->setFromDate(new \DateTime('2022-10-01 11:00'));
////		$collisionAssignment->setToDate(new \DateTime('2022-10-01 13:00'));
////
////		// Validate and try to save the collisionAssignment
////		// Assertions here for unsuccessful save or validation error
////	}
////
////	// Similar tests for RepeatedAssignment
//}