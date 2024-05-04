<?php

namespace App\Controller;

use App\Repository\FormSubmissionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeleteSubmissionController extends AbstractController
{
    #[Route('/deleteSubmission', name: 'app_delete_submission')]
    public function handleDeletion(FormSubmissionsRepository $formSubmissionRepository, Request $request): Response
    {
        /*$this->denyAccessUnlessGranted('ROLE_ADMIN');*/

        if ($request->getMethod() === 'GET' && $request->query->has('id')) {
            $submissionId = $request->query->get('id');

            // Check if the submissionId is a single id or multiple ids
            if (!str_contains($submissionId, ',')) {
                $formSubmissionRepository->deleteFormSubmission($submissionId);
            } else {
                $submissionIds = explode(",", $submissionId);
                foreach ($submissionIds as $id) {
                    $formSubmissionRepository->deleteFormSubmission($id);
                }
            }
            $page = $request->query->get('page', 1);
            return $this->redirectToRoute('customer_support', ['page' => $page]);
        } else {
            return $this->redirectToRoute('customer_support', ['invalidSubmissionId' => true]);
        }
    }
}
