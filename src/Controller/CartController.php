<?php
namespace App\Controller;

use App\Entity\Livres;
use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


}