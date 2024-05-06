<?php

namespace App\Controller;

use App\Service\NotificationService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[Route('/notifications', name: 'app_notifications')]
    public function index(): Response
    {
        $this->notificationService->createDummyNotifications(10, $this->getUser()->getId());
        $notifications = $this->notificationService->getAllNotifications($this->getUser());

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/notifications/mark-read/{id}', name: 'mark_notification_read')]
    public function markAsRead($id): Response
    {
        $this->notificationService->markNotificationAsRead($id);

        return $this->redirectToRoute('app_notifications');
    }

    /**
     * @throws Exception
     */
    #[Route('/notifications/delete/{id}', name: 'delete_notification')]
    public function delete($id): Response
    {
        $this->notificationService->deleteNotification($id);

        return $this->redirectToRoute('app_notifications');
    }

    #[Route('/notifications/delete-all', name: 'delete_all_notifications')]
    public function deleteAll(): Response
    {
        $this->notificationService->deleteAllNotifications($this->getUser()->getId());

        return $this->redirectToRoute('app_notifications');
    }
}
