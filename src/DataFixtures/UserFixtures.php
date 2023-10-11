<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\ApiTokenFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
	public function __construct(private readonly UserPasswordHasherInterface $passwordHasher
	) {
	}

    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
	    $hashedPassword = $this->passwordHasher->hashPassword($user1, 'admin');
		$user1->setEmail('admin@example.com')
			->setPassword($hashedPassword)
			->setRoles(['ROLE_ADMIN']);

	    $user2 = new User();
	    $hashedPassword = $this->passwordHasher->hashPassword($user1, 'user');
	    $user2->setEmail('user@example.com')
		    ->setPassword($hashedPassword);

        $manager->persist($user1);
	    $manager->persist($user2);

        $manager->flush();

	    ApiTokenFactory::createOne([
		    'ownedBy' => $user1,
	    ]);
    }
}
