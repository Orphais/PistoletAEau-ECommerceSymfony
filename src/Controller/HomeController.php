<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/{_locale}', name: 'home', requirements: ['_locale' => 'fr|en'])]
    public function index(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $products = $paginator->paginate(
            $productRepository->findAll(),
            $request->query->getInt('page', 1),
            16
        );

        return $this->render('pages/home/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/', name: 'home.redirect')]
    public function redirectToDefault(): Response
    {
        return $this->redirectToRoute('home', ['_locale' => 'fr']);
    }

}
