<?php

namespace App\Tests\Application;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testHomePageExists(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

	public function testHomePageElements(): void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/');

		// Assert response is successful
		$this->assertResponseIsSuccessful();

		// Assert navbar elements
		$this->assertSelectorExists('nav.navbar');

		// Assert for "Offices" in the dropdown
	    $this->assertSelectorTextContains('li.nav-item.dropdown a.nav-link.dropdown-toggle', 'Offices');

		// Assert for other elements directly under a.nav-link
		$this->assertSelectorTextContains('a.nav-link[href="/person"]', 'Persons');
		$this->assertSelectorTextContains('a.nav-link[href="/register"]', 'Register');
		$this->assertSelectorTextContains('a.nav-link[href="/login"]', 'Login');

		// Assert the welcome message
		$this->assertSelectorTextContains('h1', 'WELCOME TO SEATMASTER');
	}
}
