<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Office;
use PHPUnit\Framework\TestCase;

class OfficeTest extends TestCase
{
	public function testCanGetAndSetDate(): void
	{
		$office = new Office();
		$office->setName("Cirqus");
		$office->setHeight(400);
		$office->setWidth(500);

		$this->assertSame("Cirqus", $office->getName());
	}
}