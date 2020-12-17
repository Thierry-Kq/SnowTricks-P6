<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getPaginatedComments($page, $limit, $trickId)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.trick = :trickId')
            ->setParameter(':trickId', $trickId)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult(($page * $limit) - $limit)
            ->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }

    public function getTotalCommentsByOneTrick($trickId)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.trick = :trickId')
            ->setParameter(':trickId', $trickId)
            ->select('COUNT(c)');

        return $query->getQuery()->getSingleScalarResult();
    }
}
