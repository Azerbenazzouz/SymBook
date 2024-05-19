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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/commande', name: 'app_order_')]
class OrderController extends AbstractController
{
    #[Route('/ajout', name: 'add')]
    public function add(SessionInterface $session,LivresRepository $rep): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $panier=$session->get('panier',[]);
        if($panier === []){
            $this->addFlash('message_error','Votre panier est vide');
            return $this->redirectToRoute('app_home');
        }

        $total = 0;
        foreach ($panier as $item=>$quantity){
            $livre=$rep->find($item);
            $price=$livre->getPrix();
            $total += $price * $quantity;
        }
        
        // convertir total to string
        $total = number_format(($total)*1000,0,'.','');
        // url de base de l'application et les urls de redirection
        $baseUrl = $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $successUrl = $baseUrl . "commande/flousi/check/success";
        $faildUrl = $baseUrl . "commande/flousi/check/faild";
        //  Appel de l'API Flouci pour générer le lien de paiement
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://developers.flouci.com/api/generate_payment',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "app_token": "fc49d690-deca-424a-9f4d-60ac5e9ac7bd",
                "app_secret": "d28fc2eb-4af2-41b6-8bdb-8534031ed552",
                "accept_card": "true",
                "amount": '.$total.',
                "success_link": "'.$successUrl.'",
                "fail_link": "'.$faildUrl.'",
                "session_timeout_secs": 1200,
                "developer_tracking_id": "<your_internal_tracking_id>"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
        
        if($response['code']==0){
            $session->set('payment_id',$response['result']['payment_id']);
            return $this->redirect($response['result']['link']);
        }else{
            $this->addFlash('message_error','Commande echouée ');
            return $this->redirectToRoute('app_home');
        }       
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

     #[Route('/flousi/check/{etat}', name: 'flouci_check')]
     public function flouciCheck(SessionInterface $session,LivresRepository $rep, EntityManagerInterface $em ,$etat,Request $req): Response{

        $panier=$session->get('panier',[]);
        $payment_id=$req->query->get('payment_id');

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
        
        if($etat == "success" && $payment_id == $session->get('payment_id')){
            $em->persist($order);
            $em->flush();       
            $session->remove('panier');
            $this->addFlash('message','Commande créée avec succès ');
        }else{
            $this->addFlash('message_error','Commande echouée');
        }
        $session->remove('payment_id');
        return $this->redirectToRoute('app_home');
    }
    
}
