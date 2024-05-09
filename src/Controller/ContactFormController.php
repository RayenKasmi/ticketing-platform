<?php

namespace App\Controller;

use App\Entity\FormSubmissions;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactFormController extends AbstractController
{
    #[Route('/contact-form', name: 'app_contact_form')]
    public function index(Request $request,EntityManagerInterface $entityManager,MailerInterface $mailer): Response
    {
        $formSubmission = new FormSubmissions();
        $form = $this->createForm(ContactFormType::class, $formSubmission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //send email to ticketi873@gmail.com that contains the form data
            $email = (new Email())
                ->from(new Address('MS_gyB2gT@trial-jy7zpl93wn3l5vx6.mlsender.net',$form->get('name')->getData()))
                ->to(new Address('tickety873@gmail.com'))
                ->subject($form->get('subject')->getData())
                ->html('<p>'.$form->get('message')->getData().'</p>');

            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                // Retry for 3 times
                for ($i = 0; $i < 3; $i++) {
                    sleep(pow(2, $i)); // Exponential backoff to prevent server being flooded with retries
                    try {
                        $mailer->send($email);
                        break;
                    } catch (TransportExceptionInterface $e) {
                        if ($i === 2) {
                            $this->addFlash('error', 'An error occurred while sending the email: ' . $e->getMessage());
                        }
                    }
                }

                if ($i === 3) {
                    $this->addFlash('error', 'Failed to send email after retries.');
                }
            }

            $entityManager->persist($formSubmission);
            $entityManager->flush();
            $this->addFlash('success', 'Your message has been sent');
            return $this->redirectToRoute('app_contact_form');
        }

        return $this->render('contact_form/index.html.twig', [
            'ContactForm' => $form,
        ]);
    }
}
