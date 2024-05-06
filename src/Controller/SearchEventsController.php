<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class SearchEventsController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private CategoriesRepository $categoryRepository;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/search/{term}', name: 'app_search_events')]
    public function index(string $term): Response
    {
        try {
            $rsm = new ResultSetMappingBuilder($this->entityManager);
            $rsm->addRootEntityFromClassMetadata('App\Entity\Events', 'e');

            $searchedEvents = $this->entityManager->createNativeQuery("
            SELECT *
            FROM events e
            WHERE MATCH(e.short_description, e.long_description, e.name, e.venue, e.organizer) AGAINST (:searchTerm IN NATURAL LANGUAGE MODE)
        ", $rsm)
                ->setParameter('searchTerm', $term)
                ->getResult();
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