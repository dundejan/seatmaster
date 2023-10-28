<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Office;
use App\Repository\OfficeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OfficeTest extends KernelTestCase
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

	public function testCanGetAndSetData(): void
	{
		$office = new Office();
		$office->setName("Cirqus");
		$office->setHeight(400);
		$office->setWidth(500);

		$this->assertSame("Cirqus", $office->getName());

		$this->persistAndFlush([$office]);

		/** @var OfficeRepository $officeRepository */
		$officeRepository = $this->entityManager->getRepository(Office::class);

		$this->assertSame($office, $officeRepository->find($office->getId()));
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
}