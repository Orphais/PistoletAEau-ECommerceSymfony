<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/{_locale}/admin', name: 'admin', requirements: ['_locale' => 'fr|en'])]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        return $this->render('pages/admin/index.html.twig');
    }

    #[Route('/{_locale}/admin/users', name: 'admin.users', requirements: ['_locale' => 'fr|en'])]
    public function users(UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $users = $userRepository->findAll();

        return $this->render('pages/admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{_locale}/admin/products', name: 'admin.products', requirements: ['_locale' => 'fr|en'])]
    public function products(ProductRepository $productRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $products = $productRepository->findAll();

        return $this->render('pages/admin/products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{_locale}/admin/categories', name: 'admin.categories', requirements: ['_locale' => 'fr|en'])]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $categories = $categoryRepository->findAll();

        return $this->render('pages/admin/categories.html.twig', [
            'categories' => $categories,
        ]);
    }
}
