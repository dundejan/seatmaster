<?php

namespace App\Tests\Application;

use Doctrine\Persistence\ObjectRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Office;
use Doctrine\ORM\EntityManagerInterface;

class OfficeCreationTest extends WebTestCase
{
	/**
	 * @throws Exception
	 */
	public function testCreateOfficeAndCheckItIsShownInNavbar(): void
	{
		$client = static::createClient();
		$container = static::getContainer();

		/** @var EntityManagerInterface $entityManager */
		$entityManager = $container->get(EntityManagerInterface::class);

		// Create and persist a new Office entity
		$office = new Office();
		$office->setName('TestOffice');
		$entityManager->persist($office);
		$entityManager->flush();

		// Use the client to navigate to the homepage
		$crawler = $client->request('GET', '/');

		// Check if the Office is listed in the navbar
		$this->assertResponseIsSuccessful();
		$this->assertSelectorTextContains('a.dropdown-item', 'TestOffice');

		// Cleanup: remove the test Office entity
		/** @var ObjectRepository<Office> $officeRepository */
		$officeRepository = $entityManager->getRepository(Office::class);
		$persistedOffice = $officeRepository->findOneBy(['name' => 'TestOffice']);
		if ($persistedOffice !== null) {
			$entityManager->remove($persistedOffice);
			$entityManager->flush();
		}
	}
}
