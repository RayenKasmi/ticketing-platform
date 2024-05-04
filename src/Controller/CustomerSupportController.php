<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\FormSubmissionsRepository;

class CustomerSupportController extends AbstractController
{
    #[Route('/customer-support', name: 'customer_support', methods: ['GET'])]
    public function index(FormSubmissionsRepository $formSubmissionRepository, Request $request): Response
    {

        $maxPerPage = 10;
        $totalPages = $formSubmissionRepository->totalPages($maxPerPage);
        $currentPage = $request->query->getInt('page', 1);
        $offset = ($currentPage - 1) * $maxPerPage;
        $contactForms = $formSubmissionRepository->findBy([], null, $maxPerPage, $offset);

        return $this->render('customer_support/index.html.twig', [
            'contactForms' => $contactForms,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,

        ]);
    }
}
