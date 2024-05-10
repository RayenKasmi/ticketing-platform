<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventReservationType;
use App\Service\CurrencyConverterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class EventPageController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private CurrencyConverterService $currencyConverter;
    private int $MAX_QUERY_RESULT = 5;
    public function __construct(EntityManagerInterface $entityManager, CurrencyConverterService $currencyConverterService)
    {
        $this->entityManager = $entityManager;
        $this->currencyConverter = $currencyConverterService;
    }
    #[Route('/event/{id}', name: 'app_event_page')]
    public function index(Events $event=null, SessionInterface $session): Response
    {
        if (!$event) {
            return new Response('The event does not exist', Response::HTTP_NOT_FOUND);
        }



        // Fetch up to N events from the same category of the current event
        $currentCategoryEvents = $this->entityManager->getRepository(Events::class)->findEventsByCategoryExcludingCurrent($event, $this->MAX_QUERY_RESULT);

        $currency = $session->get('currency', 'USD');

        if ($currency !== 'USD') {
            $event->setTicketPrice($this->currencyConverter->convertPrice($event->getTicketPrice(), $currency));
            foreach ($currentCategoryEvents as $someEvent) {
                $someEvent->setTicketPrice($this->currencyConverter->convertPrice($someEvent->getTicketPrice(), $currency));
            }
        }

        $form = $this->createForm(EventReservationType::class);

        return $this->render('event_page/index.html.twig', [
            'event' => $event,
            'currentCategoryEvents' => $currentCategoryEvents,
            'form' => $form,
        ]);
    }
}
