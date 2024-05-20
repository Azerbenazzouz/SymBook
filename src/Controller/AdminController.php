<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Entity\OrderDetails;
use App\Repository\LivresRepository;
use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(OrderRepository $orderRepository,UserRepository $userRepository,OrderDetailsRepository $orderDetailsRepository,LivresRepository $livresRepository): Response
    {
        $today = new DateTime();
      
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

        $orders = $orderRepository->findAll();
        $totalOrders = count($orders);

        $mostOrderedBookData = $orderDetailsRepository->createQueryBuilder('od')
        ->select('IDENTITY(od.livre) AS bookId, SUM(od.quantity) AS totalQuantity, MAX(o.create_at) AS lastOrderDate')
        ->join('od.orders', 'o')
        ->groupBy('od.livre')
        ->orderBy('totalQuantity', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    
    // If there is a result, find the Livre entity by its ID
    $mostOrderedBook = null;
    $lastOrderDate = null;
    if ($mostOrderedBookData !== null) {
        $mostOrderedBook = $livresRepository->find($mostOrderedBookData['bookId']);
        $lastOrderDate = $mostOrderedBookData['lastOrderDate'];
    }
    



        return $this->render('admin/index.html.twig', [
            'totalMoneyToday' => $totalMoneyToday,
            'percentageChange' => $percentageChange,
            'dayOfWeek' => $dayOfWeek,
            'totalUsers' => $totalUsers, 
            'totalOrders' => $totalOrders,
            'mostOrderedBook' => $mostOrderedBook,
    'lastOrderDate' => $lastOrderDate,
    //'dayOfWeek' => (new \DateTime())->format('l'),
          
           
        ]);
    }
}

