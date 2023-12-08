<?php

namespace App\Tests\Application;

use App\Entity\Office;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminTest extends WebTestCase
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
		if (!$this->entityManager instanceof EntityManagerInterface) {
			throw new Exception('Entity manager not found');
		}

		/** @var UserPasswordHasherInterface $passwordHasher */
		$passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

		// Create a test user
		$user = new User();
		$user->setEmail('admin@example.com');
		$user->setPassword($passwordHasher->hashPassword($user, 'admin'));
		$user->setRoles(['ROLE_SUPER_ADMIN']);

		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	public function testNonAdminCanNotAccessAdmin(): void
	{
		// Request the homepage as not logged user
		$crawler = $this->client->request('GET', '/admin');

		// Assert that the response is a redirect to the login page
		$this->assertResponseRedirects('http://localhost/login', 302, 'Non-admin user is not redirected to login page.');

		// Follow the redirect and assert the URL
		$crawler = $this->client->followRedirect();
		$this->assertSame('http://localhost/login', $this->client->getRequest()->getUri(), 'Redirected to an unexpected URL.');
	}

	public function testAdminCanAccessAdminAndSidebarLinksAreVisible(): void
	{
		$this->loginAsAdmin();

		// Request the homepage as logged user with admin rights
		$crawler = $this->client->request('GET', '/admin');

		// Assert a specific 200 status code, no redirects
		$this->assertResponseStatusCodeSame(200);

		// Assert the presence of the "Statistics" link
		$this->assertSame(
			1,
			$crawler->filter('a:contains("Statistics")')->count(),
			'The link to "Statistics" was not found.'
		);

		// Assert the presence of the "Offices" link
		$this->assertSame(
			1,
			$crawler->filter('a:contains("Offices")')->count(),
			'The link to "Offices" was not found.'
		);

		// Assert the presence of the "Back to app" link
		$this->assertSame(
			1,
			$crawler->filter('a:contains("Back to app")')->count(),
			'The link to "Back to app" was not found.'
		);
	}

	public function testAdminCanClickLinkOnSidebarAndIsCorrectlyRedirected(): void
	{
		$this->loginAsAdmin();

		// Request the admin as logged user with admin rights
		$crawler = $this->client->request('GET', '/admin');

		// Find the link to "Offices"
		$link = $crawler->filter('a:contains("Offices")')->link();

		// Click the link
		$crawler = $this->client->click($link);

		// Assertions about the new page - check if the URL is correct
		$this->assertStringContainsString('/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCrudController%5COfficeCrudController', $this->client->getRequest()->getUri());
	}

	public function testAdminCanClickTheAddOfficeButtonToShowTheForm(): void
	{
		$this->loginAsAdmin();

		// Request the admin for offices as logged user with admin rights
		$crawler = $this->client->request('GET', '/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCrudController%5COfficeCrudController');

		// Find the "Add Office" button
		$button = $crawler->filter('a.action-new:contains("Add Office")')->link();

		// Click the button
		$crawler = $this->client->click($button);

		// Assertions about the new page - check if the URL is correct for the 'new' action
		$this->assertStringContainsString('/admin?crudAction=new&crudControllerFqcn=App%5CController%5CAdmin%5CCrudController%5COfficeCrudController', $this->client->getRequest()->getUri());
	}

	public function testAdminCanCreateNewOfficeAndOfficeIsAddedToDatabase(): void
	{
		$this->loginAsAdmin();

		// Navigate to the create office form in admin
		$crawler = $this->client->request(
			'GET',
			'/admin?crudAction=new&crudControllerFqcn=App%5CController%5CAdmin%5CCrudController%5COfficeCrudController'
		);

		// Select the form
		$form = $crawler->selectButton('Create')->form();

		// Set form data
		$formData = [
			'Office[name]' => 'testOffice',
			'Office[width]' => '600',
			'Office[height]' => '400',
		];

		// Submit the form
		$crawler = $this->client->submit($form, $formData);

		// Query for the new office
		$testOffice = $this->entityManager->getRepository(Office::class)->findOneBy(array('name' => 'testOffice'));

		// Assert that the office was created
		$this->assertNotNull($testOffice, 'The new office was not found in the database.');

		// Further assertions checking the width and height
		$this->assertEquals(600, $testOffice->getWidth(), 'Office width does not match.');
		$this->assertEquals(400, $testOffice->getHeight(), 'Office height does not match.');
	}

	public function loginAsAdmin(): void
	{
		// Retrieve test admin user
		$adminUser = $this->entityManager->getRepository(User::class)->findOneBy(array('email' => 'admin@example.com'));

		// Log in the user
		$this->client->loginUser($adminUser);
	}
}