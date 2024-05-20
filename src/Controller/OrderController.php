<?php

namespace App\Controller;

use App\Entity\EtatOrder;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\EtatOrderRepository;
use App\Repository\LivresRepository;
use App\Repository\OrderRepository;
use App\Repository\PayementTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/commande', name: 'app_order_')]
class OrderController extends AbstractController
{
    #[Route('/ajout', name: 'add')]
    public function add(SessionInterface $session,LivresRepository $rep,Request $req,PayementTypeRepository $repPT,EtatOrderRepository $repEO,EntityManagerInterface $em,MailerInterface $mailer): Response
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
    
        switch ($req->query->get('payment')) {
            case 'flouci':
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
            case 'cod':
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
                    $order->setPayementType($repPT->findOneBy(['type'=>'COD']));
                }
                    $order->setEtatPayement(false);
                    $order->setEtat($repEO->findOneBy(['etat'=>'en attente']));
                    $em->persist($order);
                    $em->flush();       
                    $session->remove('panier');
                    $this->addFlash('message','Commande créée avec succès ');
                    $email = (new Email())
                        ->from('SymBook@admin.com')
                        ->to($this->getUser()->getUserIdentifier())
                        ->subject('Commande créée avec succès '.$order->getReference())
                        ->text('Facture de votre commande '.$order->getReference())
                        ->html($this->renderView('cart/facture.html.twig', ['order' => $order]));
                    $mailer->send($email);
                return $this->redirectToRoute('app_home');            
            default:
                $this->addFlash('message_error','Commande echouée ');
                return $this->redirectToRoute('app_home');
        }
    }

     // Ajoutez cette méthode
     #[Route('/mes_commande', name: 'index')]
     public function index(OrderRepository $orderRepository): Response
     {
         $this->denyAccessUnlessGranted('ROLE_USER');
 
         $orders = $orderRepository->findBy(['user' => $this->getUser()],['create_at'=>'DESC']);

         return $this->render('cart/order.html.twig', [
             'orders' => $orders,
         ]);
     }

     #[Route('/flousi/check/{etat}', name: 'flouci_check')]
     public function flouciCheck(SessionInterface $session,LivresRepository $rep, EntityManagerInterface $em ,$etat,Request $req,PayementTypeRepository $repPT,EtatOrderRepository $repEO,MailerInterface $mailer): Response{

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
            $order->setPayementType($repPT->findOneBy(['type'=>'FLOUCI']));
        }
        
        if($etat == "success" && $payment_id == $session->get('payment_id')){
            $order->setEtatPayement(true);
            $order->setEtat($repEO->findOneBy(['etat'=>'confirmer']));
            $em->persist($order);
            $em->flush();       
            $session->remove('panier');
            $this->addFlash('message','Commande créée avec succès ');
            $email = (new Email())
                ->from('SymBook@admin.com')
                ->to($this->getUser()->getUserIdentifier())
                ->subject('Commande créée avec succès '.$order->getReference())
                ->text('Facture de votre commande '.$order->getReference())
                // send template
                ->html($this->renderView('cart/facture.html.twig', ['order' => $order]));
                
            $mailer->send($email);
        }else{
            $this->addFlash('message_error','Commande echouée');
        }
        $session->remove('payment_id');
        return $this->redirectToRoute('app_home');
    }
    
    #[Route('/demande/facture/{id}/{email}', name: 'demande_facture_email')]
    public function demandeFactureEmail(OrderRepository $orderRepository, $id,$email , MailerInterface $mailer ): Response
    {
        $order = $orderRepository->find($id);

        if($order == null){
            $this->addFlash('message_error','Commande introuvable');
            return $this->redirectToRoute('app_order_index');
        }
        
        $email = (new Email())
            ->from('SymBook@admin.com')
            ->to($email)
            ->subject('Facture de votre commande '.$order->getReference())
            // send template
            ->html($this->renderView('cart/facture.html.twig', ['order' => $order]));
            
        $mailer->send($email);
        
        $this->addFlash('message','Demande envoyée avec succès');
        return $this->redirectToRoute('app_order_index');
    }

    // gestion des commandes
    #[Route('/gestion', name: 'gestion')]
    public function gestion(OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $orders = $orderRepository->findAll();

        return $this->render('order/gestion.html.twig', [
            'orders' => $orders,
        ]);
    }

}
