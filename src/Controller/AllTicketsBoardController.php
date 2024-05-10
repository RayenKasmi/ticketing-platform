<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AllTicketsBoardController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('dashboard/all-tickets-board', name: 'app_all_tickets_board')]
    public function index(TicketRepository $ticketRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $tickets = $ticketRepository->findAll();
        return $this->render('all_tickets_board/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }
}