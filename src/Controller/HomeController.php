<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\LivresRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // #[Route('/', name: 'app_home')]
    // public function index(LivresRepository $rep,CategoriesRepository $rep1): Response
    // {
    //     // show the last 8 new books
    //     $livres = $rep->findBy([], ['editedAt' => 'DESC'], 8);
    //     $categories = $rep1->findAll();
    //     return $this->render('home/index.html.twig', [
    //         'controller_name' => 'HomeController',
    //         'livres' => $livres,
    //         'categories' => $categories
    //     ]);
    // }

    #[Route('/', name: 'app_home')]
     public function livre(LivresRepository $rep,PaginatorInterface $paginateur, Request $request,CategoriesRepository $rep1): Response
     {
         $data = $rep->findAll();
         $livres=$paginateur->paginate(
            $data,
            $request->query->getInt('page',1),
            8
         );

         $categories = $rep1->findAll();

         return $this->render('home/index.html.twig', [
             'livres' => $livres,
             'categories' => $categories
         ]);
     }
    // tous les livres
    #[Route('/livres', name: 'app_livres')]
    public function livres(LivresRepository $rep): Response
    {
        $livres = $rep->findAll();
        return $this->render('livres/listLivres.html.twig', [
            'livres' => $livres
        ]);
    }
    // livre par id
    #[Route('/livre/{id}', name: 'app_livre')]
    public function livreId(LivresRepository $rep, $id): Response
    {
        $livre = $rep->find($id);

        $qb = $rep->createQueryBuilder('l'); // 'l' est un alias pour 'livre'
        $livres = $qb->where('l.categorie = :categorie')
            ->andWhere('l.id != :currentBookId')
            ->setParameter('categorie', $livre->getCategorie())
            ->setParameter('currentBookId', $livre->getId())
            ->orderBy('l.editedAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        return $this->render('livres/show.html.twig', [
            'livre' => $livre,
            'livres' => $livres
        ]);
    }
}
