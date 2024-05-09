<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Service\TicketGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ManageTicketsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TicketGeneratorService $ticketGenerator;



    public function __construct(EntityManagerInterface $entityManager, TicketGeneratorService $ticketGenerator)
    {
        $this->entityManager = $entityManager;
        $this->ticketGenerator = $ticketGenerator;
    }

    #[Route('/manage-tickets', name: 'app_manage_tickets')]
    public function index(Request $request): Response
    {

        $user = $this->getUser();

        $page = $request->query->getInt('page', 1);

        $ticketsPerPage = 6;
        $offset = ($page - 1) * $ticketsPerPage;

        $ticketRepository = $this->entityManager->getRepository(Ticket::class);
        $tickets = $ticketRepository->findBy(
            ['buyer' => $user],
            ['purchase_date' => 'DESC'],
            $ticketsPerPage,
            $offset
        );

        $totalTickets = $ticketRepository->count(['buyer' => $user]);
        $totalPages = ceil($totalTickets / $ticketsPerPage);

        return $this->render('manage_tickets/index.html.twig', [
            'tickets' => $tickets,
            'current_page' => $page,
            'total_pages' => $totalPages,
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
