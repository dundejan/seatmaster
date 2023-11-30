<?php

namespace App\Tests\Application;

use App\Entity\Assignment;
use App\Entity\Office;
use App\Entity\Person;
use App\Entity\Seat;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\InputFormField;

class PersonCreationSearchShowTest extends WebTestCase
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
		$container = static::getContainer();

		$this->entityManager = $container->get(EntityManagerInterface::class);
	}

	public function testSearchForExistingPersonAndFindIt(): void
	{
		// Create and persist Person entities
		$this->createPerson('John', 'Doe', 'john@example.com', 'Plumber');
		$this->createPerson('Lady', 'Dee', 'lady@example.com', 'Assistant');

		// Use the client to navigate to the persons page
		$crawler = $this->client->request('GET', '/person');
		$this->assertResponseIsSuccessful();

		// Find the form element and get its button
		$form = $crawler->filter('form')->form();

		// Explicitly declare the type of the field
		/** @var InputFormField $searchField */
		$searchField = $form['search'];

		$searchField->setValue('John');
		$this->client->submit($form);
		$crawler = $this->client->getCrawler();

		$this->assertResponseIsSuccessful();
		// Expect just the John Doe to be in the table of Persons
		$this->assertSame(1, $crawler->filter('table.modern-table tbody tr')->count());
	}

	public function testFoundPersonIsClickableWithCorrectUrl(): void
	{
		// Create and persist Person entities
		$this->createPerson('John', 'Doe', 'john@example.com', 'Plumber');
		$this->createPerson('Lady', 'Dee', 'lady@example.com', 'Assistant');

		$crawler = $this->client->request('GET', '/person');

		$row = $crawler->filter('table.modern-table tbody tr')->first();
		$onclickAttribute = $row->attr('onclick');
		$this->assertNotEmpty($onclickAttribute, 'The onclick attribute should be present');
		preg_match("/window\.location='(.+?)'/", $onclickAttribute, $matches);
		$this->assertNotEmpty($matches[1], 'The target URL is not set in the onclick attribute');

		$this->client->request('GET', $matches[1]);
		$this->assertResponseIsSuccessful();
	}

	public function testPersonDetailsPageShowingCurrentStatusWhenPersonIsOutOfOffice(): void
	{
		// Create and persist Person entities
		$person1 = $this->createPerson('John', 'Doe', 'john@example.com', 'Plumber');
		$this->createPerson('Lady', 'Dee', 'lady@example.com', 'Assistant');

		$this->client->request('GET', '/person/' . $person1->getId());

		// Wait for the response
		$this->client->getResponse();
		$this->assertResponseIsSuccessful();

		// Use the crawler to extract the content from the response
		$crawler = $this->client->getCrawler();

		// Find the table row that contains the "Current status" information by iterating through rows
		$currentStatusRow = null;
		$crawler->filter('table.modern-table tbody tr')->each(function ($row) use (&$currentStatusRow) {
			if (str_contains($row->text(), 'Current status')) {
				$currentStatusRow = $row;
				return false; // Stop the iteration
			}
			return true;
		});

		// Extract the text from the cell next to "Current status"
		$currentStatus = trim($currentStatusRow->filter('td')->text());

		// Assert that the current status is "Out of office"
		$this->assertEquals('Out of office', $currentStatus);
	}

	public function testPersonDetailsPageShowingCurrentStatusWhenPersonIsWorkingAtOffice(): void
	{
		// Create and persist Person
		$person1 = $this->createPerson('John', 'Doe', 'john@example.com', 'Plumber');

		$this->createOfficeSeatCurrentAssignmentForPerson($person1);

		$this->client->request('GET', '/person/' . $person1->getId());

		// Wait for the response
		$this->client->getResponse();
		$this->assertResponseIsSuccessful();

		// Use the crawler to extract the content from the response
		$crawler = $this->client->getCrawler();

		// Find the table row that contains the "Current status" information by iterating through rows
		$currentStatusRow = null;
		$crawler->filter('table.modern-table tbody tr')->each(function ($row) use (&$currentStatusRow) {
			if (str_contains($row->text(), 'Current status')) {
				$currentStatusRow = $row;
				return false; // Stop the iteration
			}
			return true;
		});

		// Extract the text from the cell next to "Current status"
		$currentStatus = trim($currentStatusRow->filter('td')->text());

		// Assert that the current status is "Out of office"
		$this->assertStringContainsString('Currently sitting at seat', $currentStatus);
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

	private function createPerson(string $firstName, string $lastName, string $email, string $jobTitle): Person
	{
		$person = new Person();
		$person->setFirstName($firstName);
		$person->setLastName($lastName);
		$person->setEmail($email);
		$person->setJobTitle($jobTitle);

		$this->entityManager->persist($person);
		$this->entityManager->flush();

		return $person;
	}

	private function createOfficeSeatCurrentAssignmentForPerson(Person $person): void
	{
		$office = new Office();
		$office->setName('TestOffice');
		$this->entityManager->persist($office);

		$seat = new Seat();
		$seat->setOffice($office);
		$this->entityManager->persist($seat);

		$assignment = new Assignment();
		$assignment->setPerson($person);
		$assignment->setSeat($seat);
		$assignment->setFromDate(new DateTime('now -3 hours'));
		$assignment->setToDate(new DateTime('now +3 hours'));
		$this->entityManager->persist($assignment);

		$this->entityManager->flush();
	}
}
