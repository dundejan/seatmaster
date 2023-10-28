<?php

namespace App\Tests\Api;

use App\Entity\ApiToken;
use App\Entity\Person;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonApiTest extends WebTestCase
{
	public function testGetPerson(): void
	{
		$client = static::createClient();
		$container = $client->getContainer();

		/** @var ManagerRegistry|null $doctrine */
		$doctrine = $container->get('doctrine');
		if ($doctrine === null) {
			$this->fail('Doctrine is not available.');
		}
		/** @var EntityManagerInterface $entityManager */
		$entityManager = $doctrine->getManager();

		$person = new Person();
		$person->setFirstName('John');
		$person->setLastName('Doe');

		$entityManager->persist($person);
		$entityManager->flush();

		$personId = $person->getId();

		$client->request('GET', '/api/people/' . $personId);

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$content = $client->getResponse()->getContent();

		if ($content === false) {
			$this->fail('Response content is empty.');
		}

		$decodedContent = json_decode($content, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			$this->fail('JSON decoding failed.');
		}

		$this->assertArrayHasKey('firstName', $decodedContent);
		$this->assertArrayHasKey('lastName', $decodedContent);
		$this->assertSame('John', $decodedContent['firstName']);
		$this->assertSame('Doe', $decodedContent['lastName']);
	}

	public function testGetPersonsCollection(): void
	{
		$client = static::createClient();
		$container = $client->getContainer();

		/** @var ManagerRegistry|null $doctrine */
		$doctrine = $container->get('doctrine');
		if ($doctrine === null) {
			$this->fail('Doctrine is not available.');
		}
		/** @var EntityManagerInterface $entityManager */
		$entityManager = $doctrine->getManager();

		$person = new Person();
		$person->setFirstName('John');
		$person->setLastName('Doe');

		$entityManager->persist($person);
		$entityManager->flush();

		$client->request('GET', '/api/people');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$content = $client->getResponse()->getContent();

		if ($content === false) {
			$this->fail('Response content is empty.');
		}

		$decodedContent = json_decode($content, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			$this->fail('JSON decoding failed.');
		}

		$this->assertArrayHasKey('hydra:member', $decodedContent);
		$this->assertIsArray($decodedContent['hydra:member']);
		$this->assertArrayHasKey(0, $decodedContent['hydra:member']);
		$this->assertIsArray($decodedContent['hydra:member'][0]);
		$this->assertArrayHasKey('firstName', $decodedContent['hydra:member'][0]);
		$this->assertSame('John', $decodedContent['hydra:member'][0]['firstName']);
	}

	public function testRedirectWhenTryToPostPersonWithoutAdmin(): void
	{
		$client = static::createClient();
		$container = $client->getContainer();

		/** @var ManagerRegistry|null $doctrine */
		$doctrine = $container->get('doctrine');
		if ($doctrine === null) {
			$this->fail('Doctrine is not available.');
		}

		/** @var EntityManagerInterface $entityManager */
		$entityManager = $doctrine->getManager();

		// Create a new User entity and set its role to 'ROLE_ADMIN'.
		$user = new User();
		$user->setEmail('admin@example.com');
		$user->setRoles(['ROLE_ADMIN']);
		$user->setPassword('password');
		$token = new ApiToken();
		$token->setOwnedBy($user);

		$entityManager->persist($user);
		$entityManager->persist($token);
		$entityManager->flush();

		// Add your authentication token or any other necessary header.
		$headers = [
			'CONTENT_TYPE' => 'application/ld+json',
		];

		$client->request(
			'POST',
			'/api/people',
			[],
			[],
			$headers,
			(string)json_encode([
				'firstName' => 'Jane',
				'lastName' => 'Doe'
			])
		);

		// 302 because of the redirect to login page
		$this->assertEquals(302, $client->getResponse()->getStatusCode());
	}

	public function testPostPersonAsAdmin(): void
	{
		$client = static::createClient();
		$container = $client->getContainer();

		/** @var ManagerRegistry|null $doctrine */
		$doctrine = $container->get('doctrine');
		if ($doctrine === null) {
			$this->fail('Doctrine is not available.');
		}

		/** @var EntityManagerInterface $entityManager */
		$entityManager = $doctrine->getManager();

		// Create a new User entity and set its role to 'ROLE_ADMIN'.
		$user = new User();
		$user->setEmail('admin@example.com');
		$user->setRoles(['ROLE_ADMIN']);
		$user->setPassword('password');
		$token = new ApiToken();
		$token->setOwnedBy($user);

		$entityManager->persist($user);
		$entityManager->persist($token);
		$entityManager->flush();


		$headers = [
			'CONTENT_TYPE' => 'application/ld+json',
			'HTTP_Authorization' => 'Bearer ' . $token->getToken(),
		];

		$client->request(
			'POST',
			'/api/people',
			[],
			[],
			$headers,
			(string)json_encode([
				'firstName' => 'Jane',
				'lastName' => 'Doe'
			])
		);

		$this->assertEquals(201, $client->getResponse()->getStatusCode());

		$content = $client->getResponse()->getContent();
		if ($content === false) {
			$this->fail('Response content is empty.');
		}

		$decodedContent = json_decode($content, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			$this->fail('JSON decoding failed.');
		}

		$this->assertArrayHasKey('firstName', $decodedContent);
		$this->assertArrayHasKey('lastName', $decodedContent);
		$this->assertSame('Jane', $decodedContent['firstName']);
		$this->assertSame('Doe', $decodedContent['lastName']);
	}
}
