<?php

namespace App\Repository;

use App\Entity\FormSubmissions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormSubmissions>
 *
 * @method FormSubmissions|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormSubmissions|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormSubmissions[]    findAll()
 * @method FormSubmissions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormSubmissionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSubmissions::class);
    }

//    /**
//     * @return FormSubmissions[] Returns an array of FormSubmissions objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FormSubmissions
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
