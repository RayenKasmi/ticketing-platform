<?php

namespace App\Repository;

use App\Entity\FormSubmissions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


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
    public function totalPages($x=5):int
    {
        $qb = $this->createQueryBuilder('fs')
            ->select('COUNT(fs.id)')
            ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();
        return  ceil($count / $x);
    }
    public function deleteFormSubmission($id):void
    {
        $entityManager = $this->getEntityManager();
        $submission = $this->find($id);

        if (!$submission) {
            throw new NotFoundHttpException('Form submission with ID ' . $id . ' not found.');
        }

        $entityManager->remove($submission);
        $entityManager->flush();
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
