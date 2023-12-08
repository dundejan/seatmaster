<?php

namespace App\Tests\Application;

use App\Entity\Office;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group panther
 */
class ReactComponentsTest extends PantherTestCase
{
	/**
	 * @throws NoSuchElementException
	 * @throws TimeoutException
	 * @throws Exception
	 */
	public function testGetAllOfficeIds(): void
	{
		$client = static::createPantherClient();
		$container = self::getContainer();

		// Retrieve the entity manager
		/** @var EntityManagerInterface $entityManager @phpstan-ignore-next-line */
		$entityManager = $container->get('doctrine')->getManager();
		if (!$entityManager instanceof EntityManagerInterface) {
			throw new Exception('Entity manager not found');
		}

		// Retrieve the repository for the Office entity
		$officeRepository = $entityManager->getRepository(Office::class);

		// Create a query to select all office IDs
		$query = $officeRepository->createQueryBuilder('o')
			->select('o.id, o.name')
			->getQuery();

		// Execute the query and fetch the results
		$officeArray = $query->getArrayResult();

		// Check if there are any offices
		if (count($officeArray) > 0) {
			// Randomly select one office
			$randomOffice = $officeArray[array_rand($officeArray)];

			// Perform the test on the randomly selected office
			$crawler = $client->request('GET', '/office/' . $randomOffice['id']);
			$client->waitFor('#office-map');
			$this->assertNotEmpty($crawler->filter('#office-map')->text());

			// Wait for the span containing the office name to be present in the DOM
			$client->waitFor('.MuiTypography-root.MuiTypography-caption');

			// Get the text of the element
			$officeNameText = $crawler->filter('.MuiTypography-root.MuiTypography-caption')->text();

			// Trim and assert the text matches the name of the office in uppercase
			$this->assertSame(strtoupper($randomOffice['name']), trim($officeNameText));
		} else {
			$this->fail('No offices found in the database. Make sure the fixtures (at least OfficeFixtures) were loaded to the test database.');
		}
	}
}
