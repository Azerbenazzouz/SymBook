<?php

namespace App\Repository;

use App\Entity\Livres;
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
    public function __construct(ManagerRegistry $registry,private PaginatorInterface $paginateur)
    {
        parent::__construct($registry, Livres::class);
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

}
