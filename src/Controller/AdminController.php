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
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(OrderRepository $orderRepository,UserRepository $userRepository,OrderDetailsRepository $orderDetailsRepository,LivresRepository $livresRepository,EntityManagerInterface $em): Response
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
            'dayOfWeek' => (new \DateTime())->format('l'),
            'orders'=> $orders,

            // 'salesChart' => $this->chartSales($orderRepository,$em),
        ]);
    }

    // chart sales 
    #[Route('/chart/sales', name: 'app_chart_sales')]
    public function chartSales(OrderRepository $orderRepository,EntityManagerInterface $em): Response
    {
        // $orders = $orderRepository->createQueryBuilder('o')
        //     ->select('DATE(o.create_at) AS date, SUM(o.total) AS total')
        //     ->getQuery()
        //     ->getResult();
        
        // use Doctrine\ORM\Query\ResultSetMapping;
        //  $ordersData = $orderRepository->findAll();
        // $orders = [];

        // foreach ($ordersData as $order) {
        //     // $total = $order->getTotal();
        //     $orders[] = [
        //         'date' => $order->getCreateAt()->format('Y-m-d'),
        //         'total' => $order->getTotal(),
        //     ];
        // }


        // $rsm = new ResultSetMapping();
        // $rsm->addScalarResult('date', 'date');
        // $rsm->addScalarResult('total', 'total');
        
        // $query = $em->createNativeQuery('SELECT DATE(o.create_at) AS date, SUM(o.total) AS total FROM order o GROUP BY date', $rsm);
        // $orders = $query->getResult();
        // $em = $this->$this->getDoctrine();()->getManager();
        // $query = $em->createNativeQuery("SELECT DATE(o.create_at) AS date, SUM(o.total) AS total FROM orders o GROUP BY date")->getResult();
        // $orders = $query->getResult();

        $data = [];
        // foreach ($orders as $order) {
        //     $data[] = [
        //         'date' => $order['date'],
        //         'total' => $order['total'],
        //     ];
        // }

        return $this->json($data);
    }

    #[Route('/chartjs', name: 'app_chartjs')]
    public function __invoke(ChartBuilderInterface $chartBuilder): Response
    {
        // $package = $packageRepository->find('chartjs');

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);


        return $this->render('admin/_sales_chart.html.twig', [
            // 'package' => $package,
            'chart' => $chart,
        ]);
    }

}

