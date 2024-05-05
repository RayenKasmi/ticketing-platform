<?php

namespace App\Controller;

use App\Entity\FormSubmissions;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactFormController extends AbstractController
{
    #[Route('/contact-us', name: 'app_contact_form')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formSubmission = new FormSubmissions();
        $form = $this->createForm(ContactFormType::class, $formSubmission,);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formSubmission->setName(
                $form->get('firstname')->getData().
                $form->get('lastname')->getData()
            );
            $formSubmission->setDate(new \DateTime('now'));
            $entityManager->persist($formSubmission);
            $entityManager->flush();

            $this->addFlash('success', 'Form submitted successfully');

            return $this->redirectToRoute('app_contact_form');
        }

        return $this->render('contact_form/index.html.twig', [
            'contactForm' => $form,
        ]);
    }
}
