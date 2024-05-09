<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventPageController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private int $MAX_QUERY_RESULT = 5;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/event/{id}', name: 'app_event_page')]
    public function index(Events $event=null): Response
    {
        if (!$event) {
            return new Response('The event does not exist', Response::HTTP_NOT_FOUND);
        }
        // Fetch up to N events from the same category of the current event
        $currentCategoryEvents = $this->entityManager->getRepository(Events::class)->createQueryBuilder('e')
            ->where('e.category = :category and e.id != :id')
            ->setParameter('category', $event->getCategory())
            ->setParameter('id', $event->getId())
            ->setMaxResults($this->MAX_QUERY_RESULT)
            ->getQuery()
            ->getResult();

        $form = $this->createForm(EventReservationType::class);

        return $this->render('event_page/index.html.twig', [
            'event' => $event,
            'currentCategoryEvents' => $currentCategoryEvents,
            'form' => $form,
        ]);
    }
}
