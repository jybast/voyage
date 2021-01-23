<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Recherche les 3 derniers articles publiés
     * @return Article[] retourne un tableau d'objets Article
     */
    
    public function findBylastCreated()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.valide = :val')
            ->setParameter('val', true)
            ->orderBy('a.publierAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * recherche les articles en fontion des critères passés dan sle formulaire de recherche
     */
    public function search($criteres = null, $categorie = null){
        // Construire le queryBuilder
        $query = $this->createQueryBuilder('a');
        // on sélectionne les articles validés
        $query->where('a.valide = 1');
        // si des infos sont entrées dans le champs critères
        if($criteres !== null ){
            $query->andWhere('MATCH_AGAINST(a.titre, a.soustitre, a.contenu) AGAINST(:criteres boolean) > 0')
                ->setParameter('criteres', $criteres);
        }
        // si une catégorie est sélectionnée
        if($categorie !== null){
            // jointure sur table categorie
            $query->leftJoin('a.categories', 'c')
                ->andWhere('c.id = :id')
                ->setParameter('id', $categorie);
        }

        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
