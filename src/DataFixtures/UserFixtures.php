<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /*for($i = 200; $i < 250; $i++){
            $user = new User();
            $user->setFirstname("fn $i");
            $user->setLastname("ln $i");
            $user->setEmail("john$i.doe@example.com");
            $user->setPassword("sgdsggsqgf$i");
            $user->setRoles(['ROLE_USER']);
            $user->setVerified(true);
            $user->setActive(true);

            $manager->persist($user);
        }*/
        $manager->flush();
    }
}
