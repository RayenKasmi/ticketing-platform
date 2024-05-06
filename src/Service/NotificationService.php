<?php
namespace App\Service;

use App\Entity\Notifications;
use App\Repository\NotificationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class NotificationService
{
    private $notificationRepository;
    private $entityManager;

    public function __construct(NotificationsRepository $notificationRepository, EntityManagerInterface $entityManager)
    {
        $this->notificationRepository = $notificationRepository;
        $this->entityManager = $entityManager;
    }

    public function getAllNotifications($user): array
    {
        return $this->notificationRepository->findBy(['user' => $user]);
    }

    public function markNotificationAsRead($id): void
    {
        $notification = $this->notificationRepository->find($id);
        $notification->setRead(true);
        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    public function deleteNotification($notificationId): void
    {
        $notification = $this->entityManager->getRepository(Notifications::class)->find($notificationId);

        if (!$notification) {
            throw new Exception('Notification not found.');
        }

        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }
    public function deleteAllNotifications($userId): void
    {
        $this->notificationRepository->deleteByUserId($userId);
        $this->entityManager->flush();
    }
    public function createDummyNotifications(int $count, int $userId): void
    {
        $user = $this->entityManager->getReference('App\Entity\User', $userId);

        for ($i = 0; $i < $count; $i++) {
            $notification = new Notifications();
            $notification->setContent("Dummy Notification $i");
            $notification->setSender("Sender $i");
            $notification->setUser($user);
            $this->entityManager->persist($notification);
        }
        $this->entityManager->flush();
    }
}
