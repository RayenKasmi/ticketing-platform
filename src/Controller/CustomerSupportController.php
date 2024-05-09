<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\FormSubmissionsRepository;

class CustomerSupportController extends AbstractController
{
    #[Route('/customer-support/{page<\d+>?1}', name: 'customer_support', methods: ['GET'])]
    public function index(FormSubmissionsRepository $formSubmissionRepository, Request $request, $page): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $maxPerPage = 10;
        $totalPages = $formSubmissionRepository->totalPages($maxPerPage);
        $offset = ($page - 1) * $maxPerPage;
        $contactForms = $formSubmissionRepository->findBy([], null, $maxPerPage, $offset);

        return $this->render('customer_support/index.html.twig', [
            'contactForms' => $contactForms,
            'currentPage' => $page,
            'totalPages' => $totalPages,

        ]);
    }
}
