<?php

namespace App\Controller;

use App\Entity\EventReservation;
use App\Entity\Events;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\EventReservationType;





class EventReservationController extends AbstractController
{
    private const EXPIRATION_DURATION = 20;

    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/reserve/{id}', name: 'app_event_reservation', methods: ['POST'])]
    public function makeEventReservation(Request $request, Events $event=null): Response
    {
        if (!$event) {
            return new Response('The event does not exist', Response::HTTP_NOT_FOUND);
        }

        $currentTime = new DateTime();
        if ($event->getStartSellTime() > $currentTime) {
            $this->addFlash('error', 'Tickets for this event are not yet available for sale.');
            return new RedirectResponse($request->headers->get('referer'));
        }

        $form = $this->createForm(EventReservationType::class);
        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return new Response('An error occurred', Response::HTTP_BAD_REQUEST);
        }

        $refererUrl = $request->headers->get('referer');

        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('error', 'Access Denied! You need to be logged in.');
            return new RedirectResponse($refererUrl);
        }

        if (!$this->security->isGranted('ROLE_CUSTOMER')) {
            $this->addFlash('error', 'Only customers can buy tickets.');
            return new RedirectResponse($refererUrl);
        }

        $user = $this->getUser();
        $userId = $user->getId();

        if (!$user->isVerified()) {
            $this->addFlash('error', 'Your account is not verified.');
            return new RedirectResponse($refererUrl);
        }

        $requestedQuantity = $form->getData()->getQuantity();

        if ($requestedQuantity > $event->getAvailableTickets()) {
            $this->addFlash('error', 'Unavailable quantity.');
            return new RedirectResponse($refererUrl);
        }


        $reservationRepository = $this->entityManager->getRepository(EventReservation::class);

        $eventReservation = $reservationRepository->findOneBy([
            'user' => $userId,
            'event' => $event,
            'is_expired' => false
        ]);

        if ($eventReservation) {
            $this->addFlash('error', 'You already have an ongoing event reservation for this event.');
            return new RedirectResponse($refererUrl);
        }


        $this->entityManager->beginTransaction();
        try {
            $event->setAvailableTickets($event->getAvailableTickets() - $requestedQuantity);
            $this->entityManager->persist($event);

            $eventReservation = new EventReservation();
            $eventReservation->setEvent($event);
            $eventReservation->setUser($user);
            $eventReservation->setQuantity($requestedQuantity);
            $expiration = new DateTime();
            $expiration->modify('+' . self::EXPIRATION_DURATION . ' minutes');
            $eventReservation->setExpiration($expiration);

            $this->entityManager->persist($eventReservation);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->addFlash('error', 'An error occurred. Please try again later.');
            return new RedirectResponse($refererUrl);
        }

        return $this->redirectToRoute('app_payment', ['id' => $eventReservation->getId()]);
    }

    #[Route('/cancel-reservation/{id}', name: 'app_cancel_event_reservation',  methods: ['POST'])]
    public function cancelEventReservation(Request $request, EventReservation $eventReservation=null): Response
    {
        if (!$eventReservation || $eventReservation->isExpired()) {
            return new Response('The event reservation does not exist or is already expired', Response::HTTP_NOT_FOUND);
        }

        $loggedInUser = $this->getUser();
        if (!$loggedInUser || $eventReservation->getUser() !== $loggedInUser) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $refererUrl = $request->headers->get('referer');
        $event = $eventReservation->getEvent();
        $quantity = $eventReservation->getQuantity();

        $event->setAvailableTickets($event->getAvailableTickets() + $quantity);

        $eventReservation->setExpired(true);

        $this->entityManager->persist($event);
        $this->entityManager->persist($eventReservation);

        $this->entityManager->flush();

        return $this->redirectToRoute('home');
    }

}
