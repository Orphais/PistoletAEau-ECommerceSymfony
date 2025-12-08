<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\OrderItemRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class AdminProductController extends AbstractController
{
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

            $imageFiles = $form->get('imageFile')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/products',
                            $newFilename
                        );

                        $image = new Image();
                        $image->setUrl('/uploads/products/' . $newFilename);
                        $image->setProduct($product);
                        $product->addImage($image);

                        $entityManager->persist($image);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'upload d\'une des images.');
                    }
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été créé.');

            return $this->redirectToRoute('admin.products');
        }

        return $this->render('pages/admin/products/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{_locale}/admin/product/edit/{id}', name: 'admin.product.edit', requirements: ['_locale' => 'fr|en'])]
    public function editProduct(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $imageFiles = $form->get('imageFile')->getData();

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/products',
                            $newFilename
                        );

                        $image = new Image();
                        $image->setUrl('/uploads/products/' . $newFilename);
                        $image->setProduct($product);
                        $product->addImage($image);

                        $entityManager->persist($image);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'upload d\'une des images.');
                    }
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été modifié.');

            return $this->redirectToRoute('admin.products');
        }

        return $this->render('pages/admin/products/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/{_locale}/admin/product/delete/{id}', name: 'admin.product.delete', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function deleteProduct(Product $product, EntityManagerInterface $entityManager, OrderItemRepository $orderItemRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        $getProductInOrder = $orderItemRepository->findBy(['product' => $product]);
        if (count($getProductInOrder) > 0) {
            $this->addFlash('error', 'Le produit ne peut pas être supprimé car il est associé à des commandes.');
            return $this->redirectToRoute('admin.products');
        }

        if (count($product->getCartItems()) > 0) {
            $this->addFlash('error', 'Le produit ne peut pas être supprimé car il est présent dans des paniers actifs.');
            return $this->redirectToRoute('admin.products');
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a bien été supprimé.');

        return $this->redirectToRoute('admin.products');
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
}
