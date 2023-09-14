<?php

namespace App\DataFixtures;

use App\Entity\Office;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OfficeFixtures extends Fixture
{
	public const OFFICE_1_REFERENCE = 'main-office';
	public const OFFICE_2_REFERENCE = 'left-office-1';
	public const OFFICE_3_REFERENCE = 'left-office-2';
	public const OFFICE_4_REFERENCE = 'right-office-1';

    public function load(ObjectManager $manager): void
    {
        $office1 = new Office();
		$office1->setName('Main Office');
	    $office2 = new Office();
	    $office2->setName('Left Office 1');
	    $office3 = new Office();
	    $office3->setName('Left Office 2');
	    $office4 = new Office();
	    $office4->setName('Right Office 1');

        $manager->persist($office1);
	    $manager->persist($office2);
	    $manager->persist($office3);
	    $manager->persist($office4);

        $manager->flush();

	    $this->addReference(self::OFFICE_1_REFERENCE, $office1);
	    $this->addReference(self::OFFICE_2_REFERENCE, $office2);
	    $this->addReference(self::OFFICE_3_REFERENCE, $office3);
	    $this->addReference(self::OFFICE_4_REFERENCE, $office4);
    }
}
