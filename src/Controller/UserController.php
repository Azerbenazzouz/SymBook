<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
        ]);
    }
    // update the user from the form
    #[Route('/user/update', name: 'app_user_update')]
    public function update(): Response
    {
        $user = $this->getUser();
        return $this->render('user/update.html.twig', [
            'controller_name' => 'UserController',
            // 'user' => $user,
        ]);
    }
}
