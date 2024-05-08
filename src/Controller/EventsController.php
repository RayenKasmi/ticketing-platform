<?php

namespace App\Controller;

use App\Entity\Events;
use App\Form\EventsFormType;
use App\Repository\EventsRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
#[Route('dashboard/events')]
class EventsController extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    #[Route('/{page<\d+>?1}', name: 'events')]
    public function index(EventsRepository $eventsRepository, $page): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $maxPerPage = 20;
        $totalPages = $eventsRepository->totalPages($maxPerPage);
        $offset = ($page - 1) * $maxPerPage;
        $events = $eventsRepository->findBy([], null, $maxPerPage, $offset);
        return $this->render('events/index.html.twig', [
            'events' => $events,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    /*#[Route('/', name: 'events')]
    public function index(ManagerRegistry $doctrine, Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $repository = $doctrine->getRepository(Events::class);
        $events = $repository->findAll();
        return $this->render('events/index.html.twig', ['events' => $events]);
    }*/

    #[IsGranted('ROLE_USER')]
    #[Route('/edit/{id?0}', name: 'edit_event')]
    public function edit(Events $event = null, ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $new = false;
        if (!$event) {
            $new = true;
            $event = new Events();
        }

        $form = $this->createForm(EventsFormType::class, $event);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('imagePath')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move($this->getParameter('images_directory'), $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $event->setImagePath($newFilename);
            }

            if($new) {
                $message = "Event added successfully";
            } else {
                $message = "Event updated successfully";
            }

            $manager = $doctrine->getManager();
            $manager->persist($event);
            $manager->flush();
            $this->addFlash('success', $message );
            return $this->redirectToRoute('events');
        }
        return $this->render('events/edit-event.html.twig', [
            'controller_name' => 'EventsController',
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/delete/{id}', name: 'delete_event')]
    public function delete(Events $event, ManagerRegistry $doctrine): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        $manager = $doctrine->getManager();
        $manager->remove($event);
        $manager->flush();
        $this->addFlash('success', 'Event deleted successfully');
        return $this->redirectToRoute('events');
    }
}
