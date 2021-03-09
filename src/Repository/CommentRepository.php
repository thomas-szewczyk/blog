<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findByPostId(int $id)
    {

        $qb = $this->createQueryBuilder('c');
            $qb->select('c.id', 'c.comment', 'u.username')
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p = c.post')
                ->innerJoin('App\Entity\User', 'u', Join::WITH, 'u = c.user')
                ->where('p.id = :id')
                ->setParameter('id', $id);

        $query = $qb->getQuery();

        return $query->execute();

    }

    public function getTotalNumberByPostId(int $id): int
    {
        $qb = $this->createQueryBuilder('comment');
        $qb->select('count(comment.id)')
            ->innerJoin('comment.post', 'post')
            ->innerJoin('comment.user', 'user')
            ->where('post.id = :id')
            ->setParameter('id', $id)
            ->groupBy('comment.id')
            ->setMaxResults(1);

        $query = $qb->getQuery();

        try {
            return $query->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
