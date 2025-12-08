<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdminCategoryController extends AbstractController
{
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

    #[Route('/{_locale}/admin/category/edit/{id}', name: 'admin.category.edit', requirements: ['_locale' => 'fr|en'])]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

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
            'category' => $category,
        ]);
    }

    #[Route('/{_locale}/admin/category/delete/{id}', name: 'admin.category.delete', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        if (count($category->getProducts()) > 0) {
            $this->addFlash('error', 'La catégorie ne peut pas être supprimée car elle contient des produits.');
            return $this->redirectToRoute('admin.categories');
        }

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'La catégorie a bien été supprimée.');

        return $this->redirectToRoute('admin.categories');
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
}
