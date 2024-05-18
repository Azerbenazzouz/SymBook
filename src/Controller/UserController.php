<?php

namespace App\Controller;

use App\Form\UserInfoType;
use App\Model\UserInfoData;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function update(UserRepository $userRepository,Request $req): Response
    {
        $user = $this->getUser();
        $userInfoData = new UserInfoData();
        $formUser = $this->createForm(UserInfoType::class,$userInfoData);
        $formUser->handleRequest($req);

        if($formUser->isSubmitted()){
            $user->setEmail($userInfoData->email);
            $user->setNom($userInfoData->nom);
            $user->setAdresse($userInfoData->adresse);
            $user->setTelephone($userInfoData->telephone);
            $userRepository->updateUser($user);
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/update.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'formUser' => $formUser
        ]);
    }
}
