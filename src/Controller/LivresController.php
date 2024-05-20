<?php

namespace App\Controller;

use DateTime;
use App\Entity\Livres;
use App\Form\LivreType;
use App\Repository\LivresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class LivresController extends AbstractController
{

    #[Route('/admin/livres', name: 'admin_livres')]
    public function index(LivresRepository $rep,Request $request): Response
    {
        $searchTitle =$request->query->get('title');
        if ($searchTitle) {
            $livres = $rep->findByTitle($searchTitle);
        } else {
            $livres = $rep->findAll();
        }
        return $this->render('Livres/index.html.twig', ['livres' => $livres]);
    }

    #[Route('/admin/livres/show/{id}', name: 'admin_livres_show')]
    public function show(Livres $livre): Response
    {

        return $this->render('Livres/show.html.twig', ['livre' => $livre]);
    }
    
    #[Route('/admin/livres/delete/{id}', name: 'app_admin_livres_delete')]
    public function delete(EntityManagerInterface $em, Livres $livre): Response
    {

        $em->remove($livre);
        $em->flush();
        return $this->redirectToRoute('admin_livres');
    }

    #[Route('/admin/livres/add', name: 'admin_livres_add')]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $livre = new Livres();
        //construction de l'objet formulaire
        $form = $this->createForm(LivreType::class, $livre);
        // recupéretaion et traitement de données
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($livre);
            $em->flush();
            return $this->redirectToRoute('admin_livres');
        }

        return $this->render('livres/add.html.twig', [
            'f' => $form

        ]);
    }

    #[Route('/admin/livres/edit/{id}', name: 'admin_livres_edit')]
    public function edit(EntityManagerInterface $em, Request $request, Livres $livre): Response
    {
        //construction de l'objet formulaire
        $form = $this->createForm(LivreType::class, $livre);
        // recupéretaion et traitement de données
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($livre);
            $em->flush();
            return $this->redirectToRoute('admin_livres');
        }

        return $this->render('livres/edit.html.twig', [
            'form' => $form
        ]);
    }



    

}
