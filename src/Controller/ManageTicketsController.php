<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Service\TicketGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ManageTicketsController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private TicketGeneratorService $ticketGenerator;



    public function __construct(EntityManagerInterface $entityManager, Security $security, TicketGeneratorService $ticketGenerator)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->ticketGenerator = $ticketGenerator;
    }

    #[Route('/manage-tickets', name: 'app_manage_tickets')]
    public function index(): Response
    {

        $user = $this->getUser();

        $ticketRepository = $this->entityManager->getRepository(Ticket::class);
        $tickets = $ticketRepository->findBy(
            ['buyer' => $user],
            ['purchase_date' => 'DESC']
        );


        return $this->render('manage_tickets/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/ticket-view/{id}', name: 'app_ticket_view')]
    public function viewTicket(Ticket $ticket=null): Response
    {
        $user = $this->getUser();

        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found');
        }

        if ($ticket->getBuyer() !== $user) {
            return new Response('Unauthorized ticket', Response::HTTP_NOT_FOUND);
        }

        $pdfContent = $this->ticketGenerator->generateSingleTicket($ticket, 'view');

        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Route('/ticket-download/{id}', name: 'app_ticket_download')]
    public function downloadTicket(Ticket $ticket=null): Response
    {
        $user = $this->getUser();

        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found');
        }

        if ($ticket->getBuyer() !== $user) {
            return new Response('Unauthorized ticket', Response::HTTP_NOT_FOUND);
        }

        $pdfContent = $this->ticketGenerator->generateSingleTicket($ticket, 'download');

        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ticket.pdf"',
        ]);
    }

}
