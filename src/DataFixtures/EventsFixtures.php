<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use App\Entity\Events;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
/*        $cat = new Categories();
        $cat->setName("music");
        $manager->persist($cat);
        for ($i = 1; $i <= 50; $i++) {
            $event = new Events();
            $event->setName("Example Event $i");
            $event->setVenue("Venue $i");
            $event->setShortDescription("Short description of event $i.");
            $event->setLongDescription("Long description of event $i.");
            $event->setOrganizer("Organizer $i");
            $event->setTotalTickets(100); // Assuming 100 tickets for each event
            $event->setAvailableTickets(100); // Initially all tickets are available
            $event->setStartSellTime(new \DateTime('now'));
            $event->setEventDate(new \DateTime("+ $i days")); // Event date i days from now
            $event->setTicketPrice(20); // Assuming ticket price of $20
            $event->setCategory($cat);
            $event->setImagePath("images/rock_concert.jpg");


            $manager->persist($event);
        }
        $cat = new Categories();
        $cat->setName("art");
        $manager->persist($cat);
        $event = new Events();
        $event->setName("Example Event 1");
        $event->setVenue("Venue 1");
        $event->setShortDescription("Short description of event 1.");
        $event->setLongDescription("Long description of event 1.");
        $event->setOrganizer("Organizer 1");
        $event->setTotalTickets(100); // Assuming 100 tickets for each event
        $event->setAvailableTickets(100); // Initially all tickets are available
        $event->setStartSellTime(new \DateTime('now'));
        $event->setEventDate(new \DateTime("+ 1 days")); // Event date i days from now
        $event->setTicketPrice(20); // Assuming ticket price of $20
        $event->setCategory($cat);
        $event->setImagePath("images/rock_concert.jpg");

        $manager->persist($event);*/

        $manager->flush();
    }
}
