<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AllUsersBoardController extends AbstractController
{
    #[Route('dashboard/all-users-board/{page<\d+>?1}', name: 'app_all_users_board')]
    public function index(UserRepository $userRepository, $page): Response
    {
        $maxPerPage = 20;
        $totalPages = $userRepository->totalPages($maxPerPage);
        $offset = ($page - 1) * $maxPerPage;
        $users = $userRepository->findBy([], null, $maxPerPage, $offset);
        return $this->render('all_users_board/index.html.twig', [
            'users' => $users,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }
}
