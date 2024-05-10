<?php

namespace App\DataFixtures;

use App\Entity\Ticket;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TicketFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /*$events = $manager->getRepository('App\Entity\Events')->findAll();
        $users = $manager->getRepository('App\Entity\User')->findAll();

        // Create tickets
        foreach ($events as $event) {
            foreach ($users as $user) {
                $ticket = new Ticket();
                $ticket->setEvent($event);
                $ticket->setBuyer($user);
                $ticket->setHolderName($user->getFirstname() . ' ' . $user->getLastname());
                $ticket->setHolderNumber($user->getId());
                $ticket->setPrice($event->getTicketPrice());
                $ticket->setPurchaseDate(new \DateTime('now'));

                $manager->persist($ticket);
            }
        }*/

        $manager->flush();
    }
}
