<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Autocomplete\EntityAutocompleteField;

#[Route('/{_locale}', requirements: ['_locale' => 'fr|en'])]
final class ProductSearchController extends AbstractController
{
    #[Route('/search', name: 'product.search')]
    public function index(): Response
    {
        return $this->render('product_search/index.html.twig');
    }

    #[Route('/api/search/autocomplete', name: 'product.search.autocomplete')]
    public function autocomplete(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (strlen($query) < 2) {
            return $this->json(['results' => []]);
        }

        $products = $productRepository->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->orWhere('p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $results = array_map(function (Product $product) {
            return [
                'id' => $product->getId(),
                'text' => $product->getName(),
                'price' => $product->getPrice(),
                'image' => count($product->getImage()) > 0 ? $product->getImage()[0]->getUrl() : null,
                'stock' => $product->getStock(),
                'status' => $product->getStatus()->value,
            ];
        }, $products);

        return $this->json(['results' => $results]);
    }

    #[Route('/api/search/products', name: 'product.search.results')]
    public function searchResults(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json(['products' => [], 'count' => 0]);
        }

        $products = $productRepository->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->orWhere('p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        $results = array_map(function (Product $product) use ($request) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'image' => count($product->getImage()) > 0 ? $product->getImage()[0]->getUrl() : null,
                'stock' => $product->getStock(),
                'status' => $product->getStatus()->value,
                'url' => $this->generateUrl('product.show', [
                    '_locale' => $request->getLocale(),
                    'id' => $product->getId()
                ])
            ];
        }, $products);

        return $this->json(['products' => $results, 'count' => count($results)]);
    }
}
