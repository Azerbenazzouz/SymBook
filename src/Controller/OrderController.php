<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\LivresRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/commande', name: 'app_order_')]
class OrderController extends AbstractController
{
    #[Route('/ajout', name: 'add')]
    public function add(SessionInterface $session,LivresRepository $rep, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $panier=$session->get('panier',[]);
        if($panier === []){
            $this->addFlash('messge','Votre panier est vide');
            return $this->redirectToRoute('app_home');
        }

        $order = new Order();

        $order->setUser($this->getUser());
        $order->setReference(uniqid());


        foreach ($panier as $item=>$quantity){
            $ordeDetails=new OrderDetails();
            $livre=$rep->find($item);
            $price=$livre->getPrix();
            $ordeDetails->setLivre($livre);
            $ordeDetails->setPrice($price);
            $ordeDetails->setQuantity($quantity);

            $order->addOrderDetail($ordeDetails);

        }
        $em->persist($order);
        $em->flush();
        $session->remove('panier');


        $this->addFlash('message','Commande créée avec succès ');
        return $this->redirectToRoute('app_home');
        


        
    }

     // Ajoutez cette méthode
     #[Route('/mes_commande', name: 'index')]
     public function index(OrderRepository $orderRepository): Response
     {
         $this->denyAccessUnlessGranted('ROLE_USER');
 
         $orders = $orderRepository->findBy(['user' => $this->getUser()]);
 
         return $this->render('cart/order.html.twig', [
             'orders' => $orders,
         ]);
     }

    
}
