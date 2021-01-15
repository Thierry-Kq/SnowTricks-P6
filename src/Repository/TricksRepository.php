<?php

namespace App\Repository;

use App\Entity\Tricks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tricks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tricks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tricks[]    findAll()
 * @method Tricks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TricksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tricks::class);
    }

    public function getPaginatedTricks($page, $limit)
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult(($page * $limit) - $limit)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    public function getTotalTricks()
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t)');

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getAllActivesTricks()
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.comments', 'c')
            ->addSelect('c')
            ->innerJoin('t.author', 'a')
            ->addSelect('a')
            ->leftJoin('t.video', 'v')
            ->addSelect('v')
            ->leftJoin('t.images', 'i')
            ->addSelect('i')
            ->getQuery();
    }


    public function getAllTrick()
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.author', 'a')
            ->addSelect('a')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Tricks[] Returns an array of Tricks objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tricks
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
