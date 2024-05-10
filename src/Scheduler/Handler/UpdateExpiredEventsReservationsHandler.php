<?php

namespace App\Scheduler\Handler;


use App\Entity\EventReservation;
use App\Scheduler\Message\UpdateExpiredEventReservations;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class UpdateExpiredEventsReservationsHandler
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
    EntityManagerInterface $entityManager, LoggerInterface $logger) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function __invoke(UpdateExpiredEventReservations $message): void
    {
        $expiredReservations = $this->entityManager->getRepository(EventReservation::class)->findExpiredEventReservations();

        foreach ($expiredReservations as $reservation) {
            $reservation->setExpired(true);

            $event = $reservation->getEvent();

            $event->setAvailableTickets($event->getAvailableTickets() + $reservation->getQuantity());

            $this->entityManager->persist($reservation);
            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $this->logger->info('Scheduled job for expired event reservations executed successfully!');
    }

}