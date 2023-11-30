<?php

namespace App\Tests\Application;

use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class LoginAndNavbarLinksBasedOnLoggedUserTest extends WebTestCase
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

		// Create a admin user
		$admin = new User();
		$admin->setEmail('admin@example.com');
		$admin->setPassword($passwordHasher->hashPassword($admin, 'admin'));
		$admin->setRoles(['ROLE_SUPER_ADMIN']);

		// Create a non admin user
		$user = new User();
		$user->setEmail('user@example.com');
		$user->setPassword($passwordHasher->hashPassword($user, 'user'));

		$this->entityManager->persist($admin);
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
	}

	public function testAdminAndLogoutLinksNotVisibleForNotLoggedInUser(): void
	{
		// Request the homepage as not logged user
		$crawler = $this->client->request('GET', '/');

		// Assert that the "Admin" and "Logout" links are not present
		$this->assertSame(0, $crawler->filter('a.nav-link[href="/admin"]')->count(),
			'Admin link found but should not be.');
		$this->assertSame(0, $crawler->filter('a.nav-link[href="/logout"]')->count(),
			'Logout link found but should not be.');
	}

	public function testLogoutLinkVisibleAdminLinkNotVisibleForNotAdminUser(): void
	{
		$this->loginAsNotAdmin();

		// Request the homepage as not logged user
		$crawler = $this->client->request('GET', '/');

		// Assert that the "Admin" link is not present but "Logout" is present
		$this->assertSame(0, $crawler->filter('a.nav-link[href="/admin"]')->count(),
			'Admin link found but should not be.');
		$this->assertSame(1, $crawler->filter('a.nav-link[href="/logout"]')->count(),
			'Logout link found but should not be.');
	}

	public function testAdminAndLogoutLinksVisibleAfterAdminLogin(): void
	{
		$this->loginAsAdmin();

		// Request the homepage as logged user with admin rights
		$crawler = $this->client->request('GET', '/');

		// Assert that the "Admin" and "Logout" links are present
		$this->assertSame(1, $crawler->filter('a.nav-link[href="/admin"]')->count(),
			'Admin link not found but should be.');
		$this->assertSame(1, $crawler->filter('a.nav-link[href="/logout"]')->count(),
			'Logout link not found but should be.');
	}

	public function loginAsAdmin(): void
	{
		// Retrieve test admin user
		$adminUser = $this->entityManager->getRepository(User::class)->findOneBy(array('email' => 'admin@example.com'));

		// Log in the user
		$this->client->loginUser($adminUser);
	}

	public function loginAsNotAdmin(): void
	{
		// Retrieve test admin user
		$basicUser = $this->entityManager->getRepository(User::class)->findOneBy(array('email' => 'user@example.com'));

		// Log in the user
		$this->client->loginUser($basicUser);
	}
}
