<?php

namespace App\Repository;

use App\Entity\Livres;
use App\Model\FilterData;
use App\Model\SearchData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Livres>
 *
 * @method Livres|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livres|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livres[]    findAll()
 * @method Livres[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivresRepository extends ServiceEntityRepository
{

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry,private PaginatorInterface $paginateur)
    {
        parent::__construct($registry, Livres::class);
        $this->paginateur = $this->paginateur;

    }

//    /**
//     * @return Livres[] Returns an array of Livres objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Livres
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    // public function findBySearch2(SearchData $searchData): PaginationInterface
    // {
    //     $data=$this->createQueryBuilder('search');
    //     if(!empty($searchData->search)){
    //         $data= $data
    //         ->andWhere('search.titre LIKE :titre')
    //         ->setParameter('titre',"%{$searchData->search}%");
    //     }

    //     $data=$data
    //     ->getQuery()
    //     ->getResult();
    //     $livers= $this->paginateur->paginate($data,$searchData->page,8);
    //     return $livers;
    // }

/**
 * @return PaginationInterface<int,Livres>
 */
public function findBySearch(SearchData $searchData): PaginationInterface
{
    $data=$this->createQueryBuilder('p');
    if(!empty($searchData->q)){
        $data= $data
        ->andWhere('p.titre LIKE :titre')
        ->setParameter('titre',"%{$searchData->q}%");
    }

    $data=$data
    ->getQuery()
    ->getResult();
    $livers= $this->paginateur->paginate($data,$searchData->page,8);
    return $livers;
}

/**
 * @return PaginationInterface<int,Livres>
 */
public function findByFilter(FilterData $filterData): PaginationInterface
    {
        $data = $this->createQueryBuilder('p');
        if (!empty($filterData->titre)) {
            $data = $data
                ->andWhere('p.titre LIKE :titre')
                ->setParameter('titre', "%{$filterData->titre}%");
        }
        if (!empty($filterData->auteur)) {
            //dd($filterData);
            $data = $data
                ->andWhere('p.Auteur LIKE :auteur')
                ->setParameter('auteur', "%{$filterData->auteur}%");
        }
        if (!empty($filterData->categories)) {
            $data = $data
                ->andWhere('p.categorie IN (:categories)')
                ->setParameter('categories', $filterData->categories);
        }
        if (!empty($filterData->prixMin)) {
            $data = $data
                ->andWhere('p.prix >= :prixMin')
                ->setParameter('prixMin', $filterData->prixMin);
        }
        if (!empty($filterData->prixMax)) {
            $data = $data
                ->andWhere('p.prix <= :prixMax')
                ->setParameter('prixMax', $filterData->prixMax);
        }
        $data = $data
            ->getQuery()
            ->getResult();
        $livres = $this->paginateur->paginate($data, $filterData->page, 8);
        return $livres;


    }

    public function findByTitle(string $title)
    {
        return $this->createQueryBuilder('l')
            ->where('l.titre LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->getQuery()
            ->getResult();
    }

   
}
