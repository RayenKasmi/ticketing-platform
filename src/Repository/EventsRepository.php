<?php

namespace App\Repository;

use App\Entity\Events;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 *
 * @method Events|null find($id, $lockMode = null, $lockVersion = null)
 * @method Events|null findOneBy(array $criteria, array $orderBy = null)
 * @method Events[]    findAll()
 * @method Events[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }


    public function totalPages($x=10):int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();
        var_dump($count); // Check the value of $count
        return  ceil($count / $x);
    }


    /**
     * Find events of the same category as the given event, excluding the current event.
     *
     * @param Events $event The current event.
     * @param int $maxResults Maximum number of results to return.
     * @return Events[] The events of the same category, excluding the current event.
     */
    public function findEventsByCategoryExcludingCurrent(Events $event, int $maxResults): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.category = :category')
            ->andWhere('e.id != :eventId')
            ->setParameter('category', $event->getCategory())
            ->setParameter('eventId', $event->getId())
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    public function searchEvents(string $term): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('e.shortDescription', ':term'),
                $qb->expr()->like('e.longDescription', ':term'),
                $qb->expr()->like('e.name', ':term'),
                $qb->expr()->like('e.venue', ':term'),
                $qb->expr()->like('e.organizer', ':term')
            )
        )
            ->setParameter('term', '%' . $term . '%');

        // Execute the query
        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Events[] Returns an array of Events objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Events
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
