<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\EventsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private EventsRepository $eventRepository;
    private CategoriesRepository $categoryRepository;
    public function __construct(EventsRepository $eventRepository, CategoriesRepository $categoryRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->categoryRepository = $categoryRepository;
    }
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        try {
            $events = $this->eventRepository->findAll();
            $categories = $this->categoryRepository->findAll();

            // Initialize eventsByCategory array
            $eventsByCategory = [];

            foreach ($categories as $category) {
                $eventsByCategory[$category->getName()] = [];
            }

            // Group events by category
            foreach ($events as $event) {
                $eventsByCategory[$event->getCategory()->getName()][] = $event;
            }

            return $this->render('home/index.html.twig', [
                'events' => $events,
                'categories' => $categories,
                'eventsByCategory' => $eventsByCategory
            ]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
    }

    // currently used to redirect from root to "/home"
    public function indexRedirect(): RedirectResponse
    {
        return new RedirectResponse('/home', 301);
    }
}
