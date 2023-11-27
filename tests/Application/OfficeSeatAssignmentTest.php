<?php

namespace App\Tests\Application;

use App\Entity\Office;
use App\Entity\Person;
use App\Entity\Seat;
use App\Entity\Assignment;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\InputFormField;

class OfficeSeatAssignmentTest extends WebTestCase
{
	/* @var EntityManagerInterface $entityManager */
	private mixed $entityManager;
	private KernelBrowser $client;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		static::ensureKernelShutdown();
		$this->client = static::createClient();

		$this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
	}

	public function testSearchAndTryToFindFreeSeatWhenThereIsNoFreeSeat(): void
	{
		$this->createPersonOfficeSeatAssignments();

		// Navigate to a specific URL
		$crawler = $this->client->request('GET', '/seats-free');

		// Wait for the response
		$this->client->getResponse();
		$this->assertResponseIsSuccessful();

		// Get the current datetime and +1 hour datetime in the correct format
		$currentDateTime = new DateTime();
		$oneHourAheadDateTime = (clone $currentDateTime)->modify('+1 hour');

		// Format the DateTime objects to the appropriate format for the input fields
		$currentDateTimeFormatted = $currentDateTime->format('Y-m-d\TH:i');
		$oneHourAheadDateTimeFormatted = $oneHourAheadDateTime->format('Y-m-d\TH:i');

		// Select the form and fill in the values
		$form = $crawler->selectButton('Search')->form();

		/** @var InputFormField $fromDateTimeField */
		$fromDateTimeField = $form['fromDateTime'];
		/** @var InputFormField $toDateTimeField */
		$toDateTimeField = $form['toDateTime'];

		$fromDateTimeField->setValue($currentDateTimeFormatted);
		$toDateTimeField->setValue($oneHourAheadDateTimeFormatted);

		// Submit the form
		$this->client->submit($form);

		// Get the crawler after form submission
		$crawler = $this->client->getCrawler();

		// Find the text within the table
		$noRecordsText = $crawler->filter('table.modern-table tbody tr td')->text();

		// Assert that the text 'no records found' is present
		$this->assertSame('no records found', trim($noRecordsText));
	}

	public function testSearchAndFindFreeSeatWhenSeatIsFree(): void
	{
		$seatId = $this->createPersonOfficeSeatAssignments();

		// Navigate to a specific URL
		$crawler = $this->client->request('GET', '/seats-free');

		// Wait for the response
		$this->client->getResponse();
		$this->assertResponseIsSuccessful();

		// Get the current datetime and +1 hour datetime in the correct format
		$currentDateTime = new DateTime('now +4 hours');
		$oneHourAheadDateTime = (clone $currentDateTime)->modify('+1 hour');

		// Format the DateTime objects to the appropriate format for the input fields
		$currentDateTimeFormatted = $currentDateTime->format('Y-m-d\TH:i');
		$oneHourAheadDateTimeFormatted = $oneHourAheadDateTime->format('Y-m-d\TH:i');

		// Select the form and fill in the values
		$form = $crawler->selectButton('Search')->form();

		/** @var InputFormField $fromDateTimeField */
		$fromDateTimeField = $form['fromDateTime'];
		/** @var InputFormField $toDateTimeField */
		$toDateTimeField = $form['toDateTime'];

		$fromDateTimeField->setValue($currentDateTimeFormatted);
		$toDateTimeField->setValue($oneHourAheadDateTimeFormatted);

		// Submit the form
		$this->client->submit($form);

		// Get the crawler after form submission
		$crawler = $this->client->getCrawler();

		// Find the text within the table
		$noRecordsText = $crawler->filter('table.modern-table tbody tr td')->text();

		// Assert that the shown free seat result matches the expected seatId
		$this->assertEquals($seatId, trim($noRecordsText));
	}

	protected function tearDown(): void
	{
		// List of entity classes to clear in correct order
		$entities = [
			Assignment::class,
			Seat::class,
			Office::class,
			Person::class,
		];

		foreach ($entities as $entity) {
			$this->entityManager->getRepository($entity)
				->createQueryBuilder('e')
				->delete()
				->getQuery()
				->execute();
		}

		$this->entityManager->clear(); // Clear the EntityManager to avoid issues with cached entities
	}

	private function createPersonOfficeSeatAssignments(): int
	{
		$office = new Office();
		$office->setName('TestOffice');
		$this->entityManager->persist($office);

		$seat = new Seat();
		$seat->setOffice($office);
		$this->entityManager->persist($seat);

		$person = new Person();
		$person->setFirstName('John');
		$person->setLastName('Doe');
		$person->setEmail('john@example.com');
		$this->entityManager->persist($person);

		$assignment = new Assignment();
		$assignment->setPerson($person);
		$assignment->setSeat($seat);
		$assignment->setFromDate(new DateTime('now -3 hours'));
		$assignment->setToDate(new DateTime('now +3 hours'));
		$this->entityManager->persist($assignment);

		$this->entityManager->flush();

		return (int) $seat->getId();
	}
}
