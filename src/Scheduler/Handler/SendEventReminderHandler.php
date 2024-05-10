<?php

namespace App\Scheduler\Handler;

use App\Entity\Events;
use App\Repository\EventsRepository;
use App\Service\NotificationService;
use DateTimeImmutable;
use App\Scheduler\Message\SendEventReminder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;



#[AsMessageHandler]
class SendEventReminderHandler
{
    private LoggerInterface $logger;

    private EventsRepository $eventsRepository;

    private NotificationService $notificationService;

    public function __construct(
        LoggerInterface $logger,EventsRepository $eventsRepository, NotificationService $notificationService) {
        $this->logger = $logger;
        $this->eventsRepository = $eventsRepository;
        $this->notificationService = $notificationService;
    }

    public function __invoke(SendEventReminder $message): void
    {
        $tomorrow = new DateTimeImmutable('+1 day');

        $eventsForTomorrow = $this->eventsRepository->findEventsForTomorrow($tomorrow);
        foreach($eventsForTomorrow as $event)
        {
            foreach ($event->getTickets() as $ticket) {
                $user = $ticket->getBuyer();
                $this->notificationService->addNotification(
                    $ticket->getEvent()->getName(),
                    "Reminder: Your event " . $ticket->getEvent()->getName() . " is happening tomorrow!",
                    $user
                );
            }
        }

        $this->logger->info('SendEventReminderHandler executed successfully at ' . date('Y-m-d H:i:s'));
    }
}