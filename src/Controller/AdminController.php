<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Repository\LivresRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
  
   
    #[Route('/stats', name: 'stats')]
    public function index(OrderRepository $orderRepository,UserRepository $userRepository,LivresRepository $livresRepository): Response
    {
        $today = new DateTime();
        $yesterday = (clone $today)->sub(new DateInterval('P1D'));

        // Define the start and end of the current day
        $todayStart = (clone $today)->setTime(0, 0);
        $todayEnd = (clone $today)->setTime(23, 59, 59);

        // Define the start and end of the previous week
        $lastWeekStart = (clone $today)->sub(new DateInterval('P7D'))->setTime(0, 0);
        $lastWeekEnd = (clone $lastWeekStart)->add(new DateInterval('P6D'))->setTime(23, 59, 59);

        $ordersToday = $orderRepository->createQueryBuilder('o')
            ->where('o.create_at BETWEEN :todayStart AND :todayEnd')
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd)
            ->getQuery()
            ->getResult();

        $ordersLastWeek = $orderRepository->createQueryBuilder('o')
            ->where('o.create_at BETWEEN :lastWeekStart AND :lastWeekEnd')
            ->setParameter('lastWeekStart', $lastWeekStart)
            ->setParameter('lastWeekEnd', $lastWeekEnd)
            ->getQuery()
            ->getResult();

        $totalMoneyToday = 0;
        foreach ($ordersToday as $order) {
            $totalMoneyToday += $order->getTotal();
        }

        $totalMoneyLastWeek = 0;
        foreach ($ordersLastWeek as $order) {
            $totalMoneyLastWeek += $order->getTotal();
        }

        $percentageChange = 0;
        if ($totalMoneyLastWeek > 0) {
            $percentageChange = (($totalMoneyToday - $totalMoneyLastWeek) / $totalMoneyLastWeek) * 100;
        }

        $dayOfWeek = $today->format('l'); // Get the current day of the week

        $totalUsers = $userRepository->count([]);

        // Récupérer les commandes d'aujourd'hui
    $todayOrders = $orderRepository->findBy([
        'create_at' => new \DateTimeImmutable('today'),
    ]);

    // Récupérer les commandes d'hier
    $yesterdayOrders = $orderRepository->findBy([
        'create_at' => new \DateTimeImmutable('yesterday'),
    ]);

    // Calculer le nombre total de commandes pour aujourd'hui et hier
    $totalTodayOrders = count($todayOrders);
    $totalYesterdayOrders = count($yesterdayOrders);

    // Calculer le pourcentage de progression
    if ($totalYesterdayOrders !== 0) {
        $progressPercentage = (($totalTodayOrders - $totalYesterdayOrders) / $totalYesterdayOrders) * 100;
    } else {
        // Si le nombre de commandes hier est 0, définissez le pourcentage de progression à 100% (toutes les commandes d'aujourd'hui sont nouvelles)
        $progressPercentage = 100;
    }

    $mostOrderedBook = $livresRepository->findMostOrderedBook();


        return $this->render('admin/index.html.twig', [
            'totalMoneyToday' => $totalMoneyToday,
            'percentageChange' => $percentageChange,
            'dayOfWeek' => $dayOfWeek,
            'totalUsers' => $totalUsers, 
            'totalTodayOrders' => $totalTodayOrders,
        'progressPercentage' => $progressPercentage,
        'mostOrderedBook' => $mostOrderedBook,
        ]);
    }

    }

