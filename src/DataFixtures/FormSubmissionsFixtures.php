<?php

namespace App\DataFixtures;

use App\Entity\FormSubmissions;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FormSubmissionsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i=0; $i<50; $i++){
            $formSubmissions = new FormSubmissions();
            $formSubmissions->setName("name $i");
            $formSubmissions->setSubject("subject $i");
            $formSubmissions->setMessage("message $i");
            $formSubmissions->setDate(new DateTime());
            $manager->persist($formSubmissions);
        }
        $manager->flush();
    }
}
