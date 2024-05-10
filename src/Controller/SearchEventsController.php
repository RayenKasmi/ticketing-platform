<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\EventsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchEventsController extends AbstractController
{
    private CategoriesRepository $categoryRepository;
    private EventsRepository $eventRepository;

    public function __construct(CategoriesRepository $categoryRepository, EventsRepository $eventRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->eventRepository = $eventRepository;
    }

    #[Route('/search', name: 'app_search_events', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $term = $request->query->get('term');

        if($term === null) {
            return $this->redirectToRoute('app_home');
        }

        try {
            $searchedEvents = $this->eventRepository->searchEvents($term);
            $categories = $this->categoryRepository->findAll();

            return $this->render('search_events/index.html.twig', [
                'searchedEvents' => $searchedEvents,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
    }
}
