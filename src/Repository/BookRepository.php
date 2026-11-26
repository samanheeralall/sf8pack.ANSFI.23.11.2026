<?php

namespace App\Repository;

use App\Entity\Book;
use App\Search\BookSearchCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book[]
     */
    public function findAllWithAuthorsAndGenres(): array
    {
        return $this->createQueryBuilder('b')
            ->addSelect('authors', 'genres')
            ->leftJoin('b.authors', 'authors')
            ->leftJoin('b.genres', 'genres')
            ->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    /**
     * @return Book[]
     */
    public function search(BookSearchCriteria $criteria): array
    {
        // Jointure d'affichage : charge tous les auteurs en une requête (évite le N+1)
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.authors', 'authors')
            ->addSelect('authors');

        if ($criteria->q) {
            $qb->andWhere('b.title LIKE :q')
                ->setParameter('q', '%'.$criteria->q.'%');
        }

        if ($criteria->author) {
            // Jointure de filtre, sans addSelect : on filtre sur les auteurs sans tronquer book.authors
            $qb->innerJoin('b.authors', 'a')
                ->andWhere('a.name LIKE :author')
                ->setParameter('author', '%'.$criteria->author.'%');
        }

        if ($criteria->available) {
            $qb->andWhere('b.available = true');
        }

        return $qb->orderBy('b.title', 'ASC')->getQuery()->getResult();
    }
}
