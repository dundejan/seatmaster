<?php

namespace App\Tests\Application;

use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class LoginTest extends WebTestCase
{
	/* @var EntityManagerInterface $entityManager */
	private mixed $entityManager;
	private KernelBrowser $client;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->client = static::createClient();

		$this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
		/** @var UserPasswordHasherInterface $passwordHasher */
		$passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

		// Create a test user
		$user = new User();
		$user->setEmail('admin@example.com');
		$user->setPassword($passwordHasher->hashPassword($user, 'admin'));

		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	public function testLoginViaLoginForm(): void
	{
		// Go to the login page
		$crawler = $this->client->request('GET', '/login');

		// Fill in the form and submit it
		$form = $crawler->selectButton('Login')->form([
			'_username' => 'admin@example.com',
			'_password' => 'admin',
		]);

		$this->client->submit($form);

		// Follow redirect after login
		$crawler = $this->client->followRedirect();

		// Assert a specific 200 status code
		$this->assertResponseStatusCodeSame(200);

		// Access session from the request object
		$session = $this->client->getRequest()->getSession();

		// Check if the session contains the authentication token
		$token = $session->get('_security_main'); // Adjust the firewall name if needed

		// Assert that the token is not null, indicating the user is logged in
		$this->assertNotNull($token, 'The security token was not found in the session.');

		// More assertions can be added to verify successful login
		// e.g., check that the crawler is on a specific page or contains specific text
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		// Clean up the test user from the database
		$user = $this->entityManager->getRepository(User::class)->findOneByEmail('test@example.com');
		if ($user) {
			$this->entityManager->remove($user);
			$this->entityManager->flush();
		}

		$this->entityManager->close();
		$this->entityManager = null;
	}
}
