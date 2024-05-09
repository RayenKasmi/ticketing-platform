<?php

namespace App\Controller;

use App\Entity\EventReservation;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\TicketGeneratorService;
use Symfony\Component\Mime\Email;


class PaymentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private TicketGeneratorService $ticketGenerator;
    private MailerInterface $mailer;



    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, TicketGeneratorService $ticketGenerator, MailerInterface $mailer)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->ticketGenerator = $ticketGenerator;
        $this->mailer = $mailer;
    }

    #[Route('/payment/{id}', name: 'app_payment')]
    public function index(Request $request, EventReservation $eventReservation=null): RedirectResponse | Response
    {
        if (!$eventReservation) {
            return new Response('Unauthorized reservation', Response::HTTP_NOT_FOUND);
        }

        if ($eventReservation->getUser() !== $this->getUser()) {
            return new Response('Unauthorized reservation', Response::HTTP_FORBIDDEN);
        }

        if ($eventReservation->getExpiration() < new \DateTime() or $eventReservation->isExpired()) {
            if (!$eventReservation->isExpired()) {
                $this->entityManager->beginTransaction();
                try {
                    $eventReservation->setExpired(true);

                    $event = $eventReservation->getEvent();

                    $this->increaseAvailableTickets($event, $eventReservation->getQuantity());

                    $this->entityManager->persist($eventReservation);


                    $this->entityManager->flush();

                    $this->entityManager->commit();
                } catch (\Exception $e) {
                    $this->entityManager->rollback();
                }
            }

            $this->addFlash('error', 'Event reservation expired');
            return new RedirectResponse($this->generateUrl('app_event_page', ['id' => $eventReservation->getEvent()->getId()]));
        }

        if ($request->isMethod('POST')) {
            return $this->handlePaymentPostRequest($request, $eventReservation);
        }

        $expiration = $eventReservation->getExpiration()->format('Y-m-d H:i:s');

        return $this->render('payment/index.html.twig', [
            'quantity' => $eventReservation->getQuantity(),
            'total_price' => $eventReservation->getQuantity() * $eventReservation->getEvent()->getTicketPrice() / 100,
            'expiration' => $expiration,
            'reservation_id' => $eventReservation->getId(),
        ]);
    }

    private function createRandomTicketName(): string
    {
        return uniqid('ticket');

    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendTickets($ticketsArray, $receiver)
    {
        $receiverName = $receiver->getFirstName() . ' ' . $receiver->getLastName();

        $ticketCount = count($ticketsArray);

        if ($ticketCount > 1) {
            $subject = "Tickets for event: " . $ticketsArray[0]->getEvent()->getName();
            $messageHtml = "Dear $receiverName, <br><br> Attached are your tickets for the event: <strong>{$ticketsArray[0]->getEvent()->getName()}</strong>.";
            $messageText = "Dear $receiverName, \n\n Attached are your tickets for the event: {$ticketsArray[0]->getEvent()->getName()}.";
        } else {
            $subject = "Ticket for event: " . $ticketsArray[0]->getEvent()->getName();
            $messageHtml = "Dear $receiverName, <br><br> Attached is your ticket for the event: <strong>{$ticketsArray[0]->getEvent()->getName()}</strong>.";
            $messageText = "Dear $receiverName, \n\n Attached is your ticket for the event: {$ticketsArray[0]->getEvent()->getName()}.";
        }

        $fileName = $this->createRandomTicketName();

        $attachmentPath  = $this->ticketGenerator->generateCombinedTickets($ticketsArray, $fileName);

        $senderEmail = $this->getParameter('app.mailer_send_username');

        $email = (new Email())
            ->from(new Address($senderEmail, 'NoTicket'))
            ->to(new Address($receiver->getEmail(), $receiverName))
            ->subject($subject)
            ->html($messageHtml)
            ->text($messageText);

        $email->attachFromPath($attachmentPath);

        $this->mailer->send($email);

        if (file_exists($attachmentPath)) {
            unlink($attachmentPath);
        }
    }

    private function handlePaymentPostRequest(Request $request, EventReservation $eventReservation): RedirectResponse | Response
    {
        $requestData = $request->request->all();

        $requiredFields = ['first_names', 'last_names', 'phone_numbers', 'credit_card', 'expiration_date', 'cvv'];
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                $this->addFlash('error', 'Missing required fields');
                return new RedirectResponse($this->generateUrl('app_payment', ['id' => $eventReservation->getId()]));
            }
        }

        $firstNames = $requestData['first_names'];
        $lastNames = $requestData['last_names'];
        $phoneNumbers = $requestData['phone_numbers'];
        $creditCard = $requestData['credit_card'];
        $expirationDate = $requestData['expiration_date'];
        $cvv = $requestData['cvv'];

        $request->getSession()->set('client_information', [
            $eventReservation->getId() => [
                'first_names' => $firstNames,
                'last_names' => $lastNames,
                'phone_numbers' => $phoneNumbers,
            ]
        ]);

        $quantity = $eventReservation->getQuantity();

        $event = $eventReservation->getEvent();

        if (count($firstNames) !== $quantity || count($lastNames) !== $quantity || count($phoneNumbers) !== $quantity) {
            $this->addFlash('error', 'An error occurred, please try again');
            return new RedirectResponse($this->generateUrl('app_payment', ['id' => $eventReservation->getQuantity()]));
        }

        foreach ($phoneNumbers as $phoneNumber) {
            if (!ctype_digit($phoneNumber)) {
                $this->addFlash('error', 'Phone numbers must be integers');
                return new RedirectResponse($this->generateUrl('app_payment', ['id' => $eventReservation->getId()]));
            }
        }

        if (!$this->checkCreditCard($creditCard, $cvv, $expirationDate)) {
            $this->addFlash('error', 'Check your credit card info.');
            return $this->redirectToRoute('app_payment', [
                'id' => $eventReservation->getId(),
            ]);
        }

        $eventReservation->setExpired(true);
        $this->entityManager->persist($eventReservation);
        $this->entityManager->flush();

        $totalPrice = $eventReservation->getQuantity() * $event->getTicketPrice();

        $tickets = [];

        if ($this->processPayment($creditCard, $cvv, $expirationDate, $totalPrice)) {
            $this->entityManager->beginTransaction();

            try {
                $buyer = $eventReservation->getUser();
                $price = $event->getTicketPrice();
                $buyDate = new \DateTime();
                for ($i = 0; $i < $quantity; $i++) {
                    $firstName = $firstNames[$i];
                    $lastName = $lastNames[$i];
                    $phoneNumber = $phoneNumbers[$i];

                    $ticket = new Ticket();
                    $ticket->setEvent($event);
                    $ticket->setBuyer($buyer);
                    $ticket->setHolderName($firstName . ' ' . $lastName);
                    $ticket->setHolderNumber($phoneNumber);
                    $ticket->setPrice($price);
                    $ticket->setPurchaseDate($buyDate);

                    $this->entityManager->persist($ticket);

                    $tickets[] = $ticket;
                }
                $this->entityManager->flush();

                $this->entityManager->commit();
            } catch (\Exception $e) {
                $this->entityManager->rollback();

                $this->logger->error('Error occurred during ticket creation: ' . $e->getMessage());

                $this->refundPayment($creditCard, $totalPrice);

                $this->increaseAvailableTickets($event, $quantity);

                $this->addFlash('error', 'An error occurred.');

                return $this->redirectToRoute('app_event_page', ['id' => $event->getId()]);
            }
        } else {
            $this->addFlash('error', 'An error occurred.');

            $this->increaseAvailableTickets($event, $quantity);

            return $this->redirectToRoute('app_event_page', ['id' => $event->getId()]);
        }

        try {
            $this->sendTickets($tickets, $buyer);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Error occurred while trying to send tickets via email: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Error occurred while trying to send tickets via email: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_manage_tickets');
    }


    function checkCreditCard($creditCard, $cvv, $expirationDate)
    {
        $creditCardPattern = '/^[0-9]{15,16}$/';
        $cvvPattern = '/^[0-9]{3}$/';

        $expirationDateTime = \DateTime::createFromFormat('m/y', $expirationDate);
        if ($expirationDateTime === false || $expirationDateTime < new \DateTime()) {
            return false;
        }

        return preg_match($creditCardPattern, $creditCard) && preg_match($cvvPattern, $cvv);
    }


    private function processPayment($creditCard, $cvv, $expirationDate, $totalPrice): bool
    {
        return true;
    }
    private function refundPayment($creditCard, $totalPrice): bool
    {
        return true;
    }

    private function increaseAvailableTickets($event, $quantity): void
    {
        $event->setAvailableTickets($event->getAvailableTickets() + $quantity);
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

}
