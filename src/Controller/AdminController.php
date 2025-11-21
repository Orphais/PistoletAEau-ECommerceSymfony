<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Form\ProductType;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function users(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $users = $paginator->paginate(
            $userRepository->findAll(),
            $request->query->getInt('page', 1),
            25
        );
        $usersCount = count($userRepository->findAll());

        return $this->render('pages/admin/users/users.html.twig', [
            'users' => $users,
            'usersCount' => $usersCount,
        ]);
    }

    #[Route('/{_locale}/admin/products', name: 'admin.products', requirements: ['_locale' => 'fr|en'])]
    public function products(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $products = $paginator->paginate(
            $productRepository->findAll(),
            $request->query->getInt('page', 1),
            25
        );
        $productsCount = count($productRepository->findAll());

        return $this->render('pages/admin/products/products.html.twig', [
            'products' => $products,
            'productsCount' => $productsCount,
        ]);
    }

    #[Route('/{_locale}/admin/categories', name: 'admin.categories', requirements: ['_locale' => 'fr|en'])]
    public function categories(CategoryRepository $categoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $categories = $paginator->paginate(
            $categoryRepository->findAll(),
            $request->query->getInt('page', 1),
            25
        );
        $categoriesCount = count($categoryRepository->findAll());

        return $this->render('pages/admin/categories/categories.html.twig', [
            'categories' => $categories,
            'categoriesCount' => $categoriesCount,
        ]);
    }

    #[Route('/{_locale}/admin/orders', name: 'admin.orders', requirements: ['_locale' => 'fr|en'])]

    public function orders(OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $orders = $paginator->paginate(
            $orderRepository->findAll(),
            $request->query->getInt('page', 1),
            25
        );

        return $this->render('pages/admin/orders/orders.html.twig', [
            'orders' => $orders,
            'ordersCount' => count($orderRepository->findAll()),
        ]);
    }

    /**
     * METHODES NEW ENTITIES
     */
    #[Route('/{_locale}/admin/product/new', name: 'admin.product.new', requirements: ['_locale' => 'fr|en'])]
    public function newProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été créé.');

            return $this->redirectToRoute('admin.products');
        }

        return $this->render('pages/admin/products/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{_locale}/admin/category/new', name: 'admin.category.new', requirements: ['_locale' => 'fr|en'])]
    public function newCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a bien été créée.');

            return $this->redirectToRoute('admin.categories');
        }

        return $this->render('pages/admin/categories/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * METHODES EDIT DETAILS ENTITIES
     */

    #[Route('/{_locale}/admin/product/edit', name: 'admin.product.edit', requirements: ['_locale' => 'fr|en'])]
    public function editProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été modifié.');

            return $this->redirectToRoute('admin.products');
        }

        return $this->render('pages/admin/products/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{_locale}/admin/category/edit', name: 'admin.category.edit', requirements: ['_locale' => 'fr|en'])]
    public function editCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a bien été modifiée.');

            return $this->redirectToRoute('admin.categories');
        }

        return $this->render('pages/admin/categories/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * METHODES SHOW DETAILS ENTITIES
     */

    #[Route('/{_locale}/admin/user/{id}', name: 'admin.user.show', requirements: ['_locale' => 'fr|en'])]
    public function showUser(int $id, UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $user = $userRepository->find($id);

        return $this->render('pages/admin/users/details.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{_locale}/admin/product/{id}', name: 'admin.product.show', requirements: ['_locale' => 'fr|en'])]
    public function showProduct(int $id, ProductRepository $productRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $product = $productRepository->find($id);

        return $this->render('pages/admin/products/details.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{_locale}/admin/category/{id}', name: 'admin.category.show', requirements: ['_locale' => 'fr|en'])]
    public function showCategory(int $id, CategoryRepository $categoryRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $category = $categoryRepository->find($id);

        return $this->render('pages/admin/categories/details.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{_locale}/admin/order/{id}', name: 'admin.order.show', requirements: ['_locale' => 'fr|en'])]
    public function showOrder(int $id, OrderRepository $orderRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $order = $orderRepository->find($id);

        return $this->render('pages/admin/orders/details.html.twig', [
            'order' => $order,
        ]);
    }
}
