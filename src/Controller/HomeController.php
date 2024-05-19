<?php

namespace App\Controller;

use App\Form\FilterType;
use App\Form\SearchType;
use App\Model\FilterData;
use App\Model\SearchData;
use App\Repository\CategoriesRepository;
use App\Repository\LivresRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    
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

        $searchData= new SearchData();

        $form=$this->createForm(SearchType::class,$searchData );
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           $searchData=$form->getData();
           $searchData->page=$request->query->getInt('page',1);
           $livres = $rep->findBySearch($searchData);
        }

        $filterData = new FilterData();
        $formFilter = $this->createForm(FilterType::class, $filterData);
        $formFilter->handleRequest($request);
        //$livres = $rep->findByFilter($filterData);
        //dd($livres);
        if ($formFilter->isSubmitted()) {
            $filterData = $formFilter->getData();
            $filterData->page = $request->query->getInt('page', 1);
            $livres = $rep->findByFilter($filterData);
        }

        return $this->render('home/index.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'form'=>$form->createView(),
            'formFilter'=>$formFilter->createView(),
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

    // tous les livres
    #[Route('/', name: 'app_livre_search')]
    public function searchLivre(LivresRepository $rep,PaginatorInterface $paginateur, Request $request,CategoriesRepository $rep1): Response
    {
        $data = $rep->findAll();
        $livres=$paginateur->paginate(
           $data,
           $request->query->getInt('page',1),
           8
        );

        $categories = $rep1->findAll();

        $searchData= new SearchData();

        $form=$this->createForm(SearchType::class,$searchData );
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
           $searchData=$form->getData();
           $searchData->page=$request->query->getInt('page',1);
           $livres=$rep->findBySearch($searchData);

        //    pagination
              $livres=$paginateur->paginate(
                $livres,
                $searchData->page,
                8
              );

           return $this->render('home/index.html.twig', [
               'form'=>$form->createView(),
               'livres' => $livres,
               'categories' => $categories,

           ]);

        }

        return $this->render('home/index.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'form'=>$form->createView()
        ]);
    }

    // livres dans session
    // without root
    public function sessionLivre(Request $request)
    {
        $session = $request->getSession();
        $session->set('livre', 'livre1');
        $livre = $session->get('livre');
        return $this->render('home/index.html.twig', [
            'livre' => $livre
        ]);
    }
}
