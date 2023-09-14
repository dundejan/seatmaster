<?php

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture
{
	public const PERSON_1_REFERENCE = 'person-1';
	public const PERSON_2_REFERENCE = 'person-2';
	public const PERSON_3_REFERENCE = 'person-3';
	public const PERSON_4_REFERENCE = 'person-4';
	public const PERSON_5_REFERENCE = 'person-5';
	public const PERSON_6_REFERENCE = 'person-6';

    public function load(ObjectManager $manager): void
    {
        $person1 = new Person();
	    $person1->setFirstName('Vojta')->setLastName('Novák')->setIdExternal(14);
	    $person2 = new Person();
	    $person2->setFirstName('Martin')->setLastName('Malý')->setIdExternal(22);
	    $person3 = new Person();
	    $person3->setFirstName('Štěpán')->setLastName('Dvořák')->setIdExternal(17);
	    $person4 = new Person();
	    $person4->setFirstName('Marie')->setLastName('Svobodová')->setIdExternal(3);
	    $person5 = new Person();
	    $person5->setFirstName('Miroslav')->setLastName('Pokorný')->setIdExternal(8);
	    $person6 = new Person();
	    $person6->setFirstName('Jana')->setLastName('Novotná')->setIdExternal(9);

        $manager->persist($person1);
	    $manager->persist($person2);
	    $manager->persist($person3);
	    $manager->persist($person4);
	    $manager->persist($person5);
	    $manager->persist($person6);

        $manager->flush();

	    $this->addReference(self::PERSON_1_REFERENCE, $person1);
	    $this->addReference(self::PERSON_2_REFERENCE, $person2);
	    $this->addReference(self::PERSON_3_REFERENCE, $person3);
	    $this->addReference(self::PERSON_4_REFERENCE, $person4);
	    $this->addReference(self::PERSON_5_REFERENCE, $person5);
	    $this->addReference(self::PERSON_6_REFERENCE, $person6);
    }
}
