<?php
namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Entity\SearchData;
use Knp\Component\Pager\PaginatorInterface;

class SearchListener
{
    private $em;
    private $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->paginator = $paginator;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ($request->attributes->has('searchData')) {
            $searchData = $request->attributes->get('searchData');
            $query = $this->em->getRepository('App:Livre')->createQueryBuilder('search')
                ->andWhere('search.titre LIKE :titre')
                ->setParameter('titre', "%{$searchData->search}%")
                ->getQuery();

            $pagination = $this->paginator->paginate(
                $query, // Pass query to paginator
                $searchData->page, // Define current page
                8 // Define number of items per page
            );

            $request->attributes->set('pagination', $pagination);
        }
    }
}
?>