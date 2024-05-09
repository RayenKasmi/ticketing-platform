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

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->security->isGranted('ROLE_CUSTOMER')) {
            return $this->redirectToRoute('app_home');
        }

        $ticketRepository = $this->entityManager->getRepository(Ticket::class);
        $tickets = $ticketRepository->findBy(['buyer' => $user]);


        return $this->render('manage_tickets/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/ticket-view/{id}', name: 'app_ticket_view')]
    public function viewTicket(Ticket $ticket=null): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to view this ticket.');
        }

        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found');
        }

        if ($ticket->getBuyer() !== $user) {
            throw new AccessDeniedException('You are not authorized to view this ticket.');
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
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to download this ticket.');
        }

        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found');
        }

        if ($ticket->getBuyer() !== $user) {
            throw new AccessDeniedException('You are not authorized to download this ticket.');
        }

        $pdfContent = $this->ticketGenerator->generateSingleTicket($ticket, 'download');

        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ticket.pdf"',
        ]);
    }

}
