<?php

namespace App\Repository;

use App\Dto\Filter\ArticleFilterDto;
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * custom requete sql
 * @extends ServiceEntityRepository<Article>
 * 
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * requete qui compte le nombre total du résultat 
     * @return bool|float|int|string|null
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult(); //renvoie juste un nombre
    }

    /**
     * Query 
     * @param \App\Dto\Filter\ArticleFilterDto $filter
     * @return array{items: mixed, meta: array{pages: float, total: bool|float|int|string|null}}
     */
    public function findPaginate(ArticleFilterDto $filter): array //retournera toujours un tableau vide
    {
        $offset = ($filter->getPage() - 1) * $filter->getLimit();
        $query = $this->createQueryBuilder('a') //createQueryBuilder à partient à la class parente ServiceEntityRepository
            ->setMaxResults($filter->getLimit())
            ->setFirstResult($offset);

        $total = $this->countAll();

        return [
            'items' => $query->getQuery()->getResult(), //resultat de la requetes
            'meta' => [
                'pages' => ceil($total / $filter->getLimit()),
                'total' => $total
            ] //information utile du resultat
        ];
    }
    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value) 
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10) //limit
    //            ->getQuery() //transforme requette en chaine de caractere
    //            ->getResult() //recupere le resultat de la requête sous forme de tableau
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
