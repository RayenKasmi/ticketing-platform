<?php

namespace App\Controller;

use App\Service\NotificationService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[Route('/notifications/{page<\d+>?1}', name: 'app_notifications')]
    public function index($page): Response
    {
/*        $this->notificationService->createDummyNotifications(10, $this->getUser()->getId()); // habetch tekhdemli el fixtures khater lezemni users donc stamaalt hedhi juste bch ntesti beha commenteha ken theb*/
        $currentPage = $page;
        $maxPerPage = 20;
        $notifications = $this->notificationService->getNotificationsByPage($currentPage,$this->getUser(),$maxPerPage);
        $totalPages = $this->notificationService->getTotalPages($this->getUser()->getId(),$maxPerPage);

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
        ]);
    }

    #[Route('/notifications/{page}/mark-read/{id}', name: 'mark_notification_read')]
    public function markAsRead($id,$page): Response
    {
        $this->notificationService->markNotificationAsRead($id);

        return $this->redirectToRoute('app_notifications', ['page' => $page]);
    }

    /**
     * @throws Exception
     */
    #[Route('/notifications/{page}/delete/{id}', name: 'delete_notification')]
    public function delete($id, $page): Response
    {
        $this->notificationService->deleteNotification($id);

        return $this->redirectToRoute('app_notifications', ['page' => $page]);
    }

    #[Route('/notifications/delete-all', name: 'delete_all_notifications')]
    public function deleteAll(): Response
    {
        $this->notificationService->deleteAllNotifications($this->getUser()->getId());

        return $this->redirectToRoute('app_notifications');
    }
}
