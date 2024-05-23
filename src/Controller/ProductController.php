<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{

    #[Route('/products', name: 'app_product')]
    public function index(Request $request, ProductRepository $productRepository, PaginatorInterface $paginator): Response
    {
        if ($request->isXmlHttpRequest()) {
            $queryBuilder = $productRepository->createQueryBuilder('p');

            if ($searchTerm = $request->query->get('searchTerm')) {
                $queryBuilder->where('p.title LIKE :search OR p.description LIKE :search OR p.weight LIKE :search OR p.category LIKE :search')
                    ->setParameter('search', '%' . $searchTerm . '%');
            }

            if ($sortColumn = $request->query->get('sortColumn')) {
                $sortDirection = $request->query->get('sortDirection', 'asc');
                $queryBuilder->orderBy('p.' . $sortColumn, $sortDirection);
            }

            $products = $paginator->paginate(
                $queryBuilder,
                ($request->query->getInt('start', 0) / $request->query->getInt('length', 10)) + 1,
                $request->query->getInt('length', 10)
            );
            $data = [];
            foreach ($products->getItems() as $entity) {
                $data[] = [
                    'title' => $entity->getTitle(),
                    'description' => $entity->getDescription(),
                    'weight' => $entity->getWeight(),
                    'category' => $entity->getCategory(),
                ];
            }

            return new JsonResponse([
                'draw' => $request->query->getInt('draw'),
                'recordsTotal' => $products->getTotalItemCount(),
                'recordsFiltered' => $products->getTotalItemCount(),
                'data' => $data,
            ]);
        }
        return $this->render('product/index.html.twig');
    }
}
