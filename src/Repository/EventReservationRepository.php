<?php

namespace App\Repository;

use App\Entity\EventReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventReservation>
 *
 * @method EventReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventReservation[]    findAll()
 * @method EventReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventReservation::class);
    }

//    /**
//     * @return EventReservation[] Returns an array of EventReservation objects
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

//    public function findOneBySomeField($value): ?EventReservation
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
