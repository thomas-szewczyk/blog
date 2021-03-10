<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findAllPosts(): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.id', 'post.title', 'post.description', 'post.imageFile','user.username as author','post.createdAt as pubDate', 'category.name as cat', 'count(distinct comment.id) as comments', 'count(distinct l.id) as likes')
            ->leftJoin('post.comments', 'comment')
            ->leftJoin('post.likes', 'l')   // 'l' used instead of 'like' because it's a sql keyword
            ->leftJoin('post.user', 'user')
            ->leftJoin('post.category', 'category')
            ->groupBy('post.id')
            ->orderBy('post.createdAt', 'DESC');

            dump($qb->getQuery()->getResult()); // For debugging

            return $qb->getQuery()
                ->getResult();
    }

    public function findAllPostsAsEntities(): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->groupBy('post.id')
            ->orderBy('post.createdAt', 'DESC');

        dump($qb->getQuery()->getResult()); // For debugging

        return $qb->getQuery()
            ->getResult();
    }

    /*
     * @param $createdBy
     * @return Post[] Returns an array of Post objects
     */
    public function findByCreator(string $createdBy): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.id', 'post.title', 'post.description','post.createdAt', 'count(distinct comment.id) as comments', 'count(distinct l.id) as likes')
            ->innerJoin('post.user', 'user')
            ->leftJoin('post.comments', 'comment')
            ->leftJoin('post.likes', 'l')
            ->groupBy('post.id')
            ->where('user.username = :name')
            ->setParameter('name', $createdBy);

        return $qb->getQuery()->getResult();
    }

    public function findLatestPosts(int $size = 3): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.id', 'post.title', 'post.description', 'post.imageFile','user.username as author','category.name as cat', 'post.createdAt as pubDate', 'count(distinct comment.id) as comments', 'count(distinct l.id) as likes')
            ->leftJoin('post.comments', 'comment')
            ->leftJoin('post.likes', 'l')
            ->leftJoin('post.user', 'user')
            ->leftJoin('post.category', 'category')
            ->groupBy('post.id')
            ->orderBy('post.createdAt', 'DESC')
            ->setMaxResults($size);

        dump($qb->getQuery()->getResult()); // For debugging

        if($size > 1) {
            return $qb->getQuery()->getResult();
        }

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return array();
        }

    }

    public function findMostLiked(): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.title', 'count(l.id) as likes')
            ->leftJoin('post.likes', 'l')
            ->groupBy('post.title')
            ->orderBy('count(l.id)', 'DESC')
            ->setMaxResults(1);

        dump($qb->getQuery()->getResult());     // For debugging

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return array(
                'title' => 'Nothing posted yet...',
                'likes' => 0);
        }
    }

    public function findMostLikedByUsername(string $createdBy): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.title', 'count(l.id) as likes')
            ->leftJoin('post.likes', 'l')
            ->innerJoin('post.user', 'user')
            ->where('user.username = :username')
            ->setParameter('username', $createdBy)
            ->groupBy('post.title')
            ->orderBy('count(l.id)', 'DESC')
            ->setMaxResults(1);

   //     dump($qb->getQuery()->getSingleResult());     // For debugging

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return array(
                'title' => 'Nothing posted yet',
                'likes' => 0);
        }
    }

    public function findMostCommentedByUsername(string $createdBy): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.title', 'count(comment.id) as comments')
            ->leftJoin('post.comments', 'comment')
            ->innerJoin('post.user', 'user')
            ->where('user.username = :username')
            ->setParameter('username', $createdBy)
            ->groupBy('post.title')
            ->orderBy('count(comment.id)', 'DESC')
            ->setMaxResults(1);

 //       dump($qb->getQuery()->getResult());     // For debugging

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return array(
                'title' => 'Nothing posted yet',
                'comments' => 0);
        }
    }

    public function countAvgLikes(string $createdBy): float
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('(count(l.id) / count(distinct post.id)) as avg')
            ->leftJoin('post.likes', 'l')
            ->innerJoin('post.user', 'user')
            ->where('user.username = :username')
            ->setParameter('username', $createdBy)
            ->groupBy('user.username')
            ->setMaxResults(1);

  //      dump($qb->getQuery()->getSingleScalarResult());     // For debugging

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0.0;
        }
    }

    public function findMostCommented(): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.title', 'count(comment.id) as comments')
            ->leftJoin('post.comments', 'comment')
            ->groupBy('post.title')
            ->orderBy('count(comment.id)', 'DESC')
            ->setMaxResults(1);

     //   dump($qb->getQuery()->getSingleResult());     // For debugging

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return array(
                'title' => 'Nothing posted yet...',
                'comments' => 0
            );
        }
    }

    public function getNumberOfPostsByUser(string $createdBy): int
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('count(post.id) as total')
            ->innerJoin('post.user', 'user')
            ->where('user.username = :username')
            ->setParameter('username', $createdBy)
            ->groupBy('user.username');

   //     dump($qb->getQuery()->getResult());     // For debugging

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    public function findAllPostsByCategory(int $id): array
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post.id', 'post.title', 'post.description', 'post.imageFile','user.username as author','post.createdAt as pubDate', 'category.name as cat', 'category.name')
            ->leftJoin('post.user', 'user')
            ->leftJoin('post.category', 'category')
            ->where('category.id = :catId')
            ->setParameter('catId', $id)
            ->groupBy('post.id')
            ->orderBy('post.createdAt', 'DESC');

        dump($qb->getQuery()->getResult()); // For debugging

        return $qb->getQuery()
            ->getResult();

    }

}
