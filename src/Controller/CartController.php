<?php
namespace App\Controller;

use App\Entity\Livres;
use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/add/{id}', name: 'add')]
    public function add(Livres $livre , SessionInterface $session )
    {
       $id=$livre->getId();
       $panier=$session->get('panier',[]);
       if(empty($panier[$id])){
        $panier[$id]=1;
       }else{
        $panier[$id]++;
       }
       $session->set('panier',$panier);

       return $this->redirectToRoute('cart_index');


      
    }

    #[Route('/', name: 'index')]
    public function index(SessionInterface $session,LivresRepository $rep  ): Response
    {
        $panier=$session->get('panier',[]);
        $data=[];
        $total=0;
  ;
        foreach($panier as $id=>$qte){
            $livre= $rep->find($id);
            $data[]=[
                'livre'=>$livre,
                'qte'=>$qte,
            
            ];
            $total+= $livre->getPrix() * $qte;
          
        }
       
        return $this->render('cart/index.html.twig',compact('data','total'));
         
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Livres $livre , SessionInterface $session )
    {
       $id=$livre->getId();
       $panier=$session->get('panier',[]);

       if(!empty($panier[$id])){
        if($panier[$id]>1){
        $panier[$id]--;
       }else{
        unset($panier[$id]);
       }
       }


       $session->set('panier',$panier);

       return $this->redirectToRoute('cart_index');


      
    }

    
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Livres $livre , SessionInterface $session )
    {
       $id=$livre->getId();
       $panier=$session->get('panier',[]);

       if(!empty($panier[$id])){
       
        unset($panier[$id]);
       
       }


       $session->set('panier',$panier);

       return $this->redirectToRoute('cart_index');
    }

    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session )
    {
      $session->remove('panier');

      return $this->redirectToRoute('cart_index');

      
    }


    #[Route('/get', name: 'get')]
    public function get(SessionInterface $session, LivresRepository $rep): Response {
        $panier = $session->get('panier', []);
        $data = [];
        $total = 0;
        foreach ($panier as $id => $qte) {
            $livre = $rep->find($id);
            $data[] = [
                'id' => $livre->getId(),
                'image' => $livre->getImage(),
                'titreLivre' => $livre->getTitre(),
                'editeur' => $livre->getEditeur(),
                'prix' => $livre->getPrix(),
                'qte' => $qte,
            ];
            $total += $livre->getPrix() * $qte;
        }
        return new JsonResponse(['data' => $data, 'total' => $total]);
    }

    #[Route('/api_add/{id}', name: 'api_add')]
    public function apiAdd(Livres $livre, SessionInterface $session): Response {
        $id = $livre->getId();
        $panier = $session->get('panier', []);
        $total = 0;
        foreach ($panier as $id => $qte) {
            $total += $livre->getPrix() * $qte;
        }
        if (empty($panier[$id])) {
            $panier[$id] = 1;
        } else {
            $panier[$id]++;
        }
        
        $session->set('panier', $panier);
        return new JsonResponse([
            'id' => $livre->getId(),
            'image' => $livre->getImage(),
            'titreLivre' => $livre->getTitre(),
            'editeur' => $livre->getEditeur(),
            'prix' => $livre->getPrix(),
            'qte' => $panier[$id],
            'total' => $total + $livre->getPrix(),
         ]);
    }

    #[Route('/api_remove/{id}', name: 'api_remove')]
    public function apiRemove(Livres $livre, SessionInterface $session): Response {
        $id = $livre->getId();
        $panier = $session->get('panier', []);
        $total = 0;
        foreach ($panier as $id => $qte) {
            $total += $livre->getPrix() * $qte;
        }
        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }
        $session->set('panier', $panier);
        return new JsonResponse([
            'id' => $livre->getId(),
            'image' => $livre->getImage(),
            'titreLivre' => $livre->getTitre(),
            'editeur' => $livre->getEditeur(),
            'prix' => $livre->getPrix(),
            'qte' => $panier[$id] ?? 0,
            'total' => $total - $livre->getPrix(),
        ]);
    }

    #[Route('/api_qte/{id}/{qte}', name: 'api_qte')]
    public function apiQte(Livres $livre, SessionInterface $session, int $qte): Response {
        $id = $livre->getId();
        $panier = $session->get('panier', []);
        $total = 0;
        foreach ($panier as $id => $qte) {
            $total += $livre->getPrix() * $qte;
        }
        if (!empty($panier[$id])) {
            if ($qte != 0) {
                $panier[$id]=$qte;
            } else {
                unset($panier[$id]);
            }
        }
        $session->set('panier', $panier);
        return new JsonResponse([
            'id' => $livre->getId(),
            'image' => $livre->getImage(),
            'titreLivre' => $livre->getTitre(),
            'editeur' => $livre->getEditeur(),
            'prix' => $livre->getPrix(),
            'qte' => $panier[$id] ?? 0,
            'total' => $total - $livre->getPrix(),
        ]);
    }


    #[Route('/api_delete/{id}', name: 'api_delete')]
    public function apiDelete(Livres $livre, SessionInterface $session): Response {
        $id = $livre->getId();
        $panier = $session->get('panier', []);
        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }
        $session->set('panier', $panier);
        return new JsonResponse(['id' => $id]);
    }

    #[Route('/api_empty', name: 'api_empty')]
    public function apiEmpty(SessionInterface $session): Response {
        $session->remove('panier');
        return new JsonResponse();
    }

    #[Route('/api_total', name: 'api_total')]
    public function apiTotal(SessionInterface $session, LivresRepository $rep): Response {
        $panier = $session->get('panier', []);
        $total = 0;
        foreach ($panier as $id => $qte) {
            $livre = $rep->find($id);
            $total += $livre->getPrix() * $qte;
        }
        return new JsonResponse(['total' => $total]);
    }


}
