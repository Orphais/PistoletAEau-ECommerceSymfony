<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Form\AddressType;
use App\Repository\CartRepository;
use App\Repository\CartItemRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/{_locale}/cart', name: 'cart', requirements: ['_locale' => 'fr|en'])]
    public function cart(CartRepository $cartRepository, ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour gérer votre panier.');
            return $this->redirectToRoute('security.login');
        }

        $cart = $cartRepository->findActiveCartByUser($this->getUser());

        return $this->render('pages/home/cart.html.twig', [
            'cart' => $cart
        ]);
    }

    #[Route('/{_locale}/address', name: 'address', requirements: ['_locale' => 'fr|en'])]
    public function address(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour gérer votre adresse.');
            return $this->redirectToRoute('security.login');
        }

        $cart = $cartRepository->findActiveCartByUser($this->getUser());
        $total = $cart->getTotalPrice();


        $address = $user->getAddress();
        if (!$address) {
            $address = new Address();
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$address->getId()) {
                $entityManager->persist($address);
                $user->setAddress($address);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Adresse enregistrée avec succès !');

            return $this->redirectToRoute('recap', ['_locale' => $request->getLocale()]);
        }

        return $this->render('pages/home/address.html.twig', [
            'form' => $form,
            'total' => $total,
        ]);
    }

    #[Route('/{_locale}/recap', name: 'recap', requirements: ['_locale' => 'fr|en'])]
    public function checkoutRecap(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('security.login');
        }

        $address = $user->getAddress();
        if (!$address) {
            $this->addFlash('error', 'Veuillez renseigner votre adresse de livraison.');
            return $this->redirectToRoute('address', ['_locale' => 'fr']);
        }

        $cart = $cartRepository->findActiveCartByUser($user);
        if (!$cart || $cart->getItems()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart', ['_locale' => 'fr']);
        }

        return $this->render('pages/home/checkout_recap.html.twig', [
            'user' => $user,
            'address' => $address,
            'cart' => $cart,
        ]);
    }

    #[Route('/', name: 'home.redirect')]
    public function redirectToDefault(): Response
    {
        return $this->redirectToRoute('home', ['_locale' => 'fr']);
    }

    #[Route('/{_locale}/search', name: 'search', requirements: ['_locale' => 'fr|en'])]
    public function search(): Response
    {
        return $this->render('pages/home/search.html.twig');
    }

    #[Route('/{_locale}/product/{id}', name: 'product.show', requirements: ['_locale' => 'fr|en'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }

        return $this->render('pages/home/details.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{_locale}/cart/add/{id}', name: 'cart.add', requirements: ['_locale' => 'fr|en'])]
    public function addToCart(
        int $id,
        ProductRepository $productRepository,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter des produits au panier.');
            return $this->redirectToRoute('security.login');
        }

        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }

        $quantity = $request->request->getInt('quantity', 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($product->getStock() <= 0) {
            $this->addFlash('error', 'Ce produit n\'est plus en stock.');
            return $this->redirectToRoute('product.show', [
                '_locale' => $request->getLocale(),
                'id' => $id
            ]);
        }

        if ($quantity > $product->getStock()) {
            $this->addFlash('error', 'Stock insuffisant. Seulement ' . $product->getStock() . ' unité(s) disponible(s).');
            return $this->redirectToRoute('product.show', [
                '_locale' => $request->getLocale(),
                'id' => $id
            ]);
        }

        $cart = $cartRepository->findActiveCartByUser($user);
        $isNewCart = false;
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
            $isNewCart = true;
        }

        if ($isNewCart) {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $entityManager->persist($cartItem);
        } else {
            $cartItem = $cartItemRepository->findByCartAndProduct($cart, $product);
            if ($cartItem) {
                $newQuantity = $cartItem->getQuantity() + $quantity;
                if ($newQuantity > $product->getStock()) {
                    $this->addFlash('error', 'Stock insuffisant. Seulement ' . $product->getStock() . ' unité(s) disponible(s).');
                    return $this->redirectToRoute('product.show', [
                        '_locale' => $request->getLocale(),
                        'id' => $id
                    ]);
                }
                $cartItem->setQuantity($newQuantity);
            } else {
                $cartItem = new CartItem();
                $cartItem->setCart($cart);
                $cartItem->setProduct($product);
                $cartItem->setQuantity($quantity);
                $entityManager->persist($cartItem);
            }
        }

        $cart->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));

        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté au panier avec succès !');

        return $this->redirectToRoute('product.show', [
            '_locale' => $request->getLocale(),
            'id' => $id
        ]);
    }

    #[Route('/{_locale}/cart/update/{id}', name: 'cart.update', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function updateCart(
        int $id,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('security.login');
        }

        $cartItem = $cartItemRepository->find($id);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        $action = $request->request->get('action');

        if ($action === 'increase') {
            if ($cartItem->getQuantity() < $cartItem->getProduct()->getStock()) {
                $cartItem->setQuantity($cartItem->getQuantity() + 1);
                $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
                $entityManager->flush();
                $this->addFlash('success', 'Quantité mise à jour');
            } else {
                $this->addFlash('error', 'Stock insuffisant');
            }
        } elseif ($action === 'decrease') {
            if ($cartItem->getQuantity() > 1) {
                $cartItem->setQuantity($cartItem->getQuantity() - 1);
                $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
                $entityManager->flush();
                $this->addFlash('success', 'Quantité mise à jour');
            }
        }

        return $this->redirectToRoute('cart', ['_locale' => $request->getLocale()]);
    }

    #[Route('/{_locale}/cart/remove/{id}', name: 'cart.remove', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function removeFromCart(
        int $id,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('security.login');
        }

        $cartItem = $cartItemRepository->find($id);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        $cart = $cartItem->getCart();
        $entityManager->remove($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
        $entityManager->flush();

        $this->addFlash('success', 'Article retiré du panier');

        return $this->redirectToRoute('cart', ['_locale' => $request->getLocale()]);
    }

    #[Route('/api/{_locale}/cart/add/{id}', name: 'api.cart.add', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function apiAddToCart(
        int $id,
        ProductRepository $productRepository,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }

        $quantity = $request->request->getInt('quantity', 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($product->getStock() <= 0) {
            return new JsonResponse(['error' => 'Ce produit n\'est plus en stock'], 400);
        }

        if ($quantity > $product->getStock()) {
            return new JsonResponse([
                'error' => 'Stock insuffisant. Seulement ' . $product->getStock() . ' unité(s) disponible(s)'
            ], 400);
        }

        $cart = $cartRepository->findActiveCartByUser($user);
        $isNewCart = false;
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
            $isNewCart = true;
        }

        if ($isNewCart) {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $entityManager->persist($cartItem);
        } else {
            $cartItem = $cartItemRepository->findByCartAndProduct($cart, $product);
            if ($cartItem) {
                $newQuantity = $cartItem->getQuantity() + $quantity;
                if ($newQuantity > $product->getStock()) {
                    return new JsonResponse([
                        'error' => 'Stock insuffisant. Seulement ' . $product->getStock() . ' unité(s) disponible(s)'
                    ], 400);
                }
                $cartItem->setQuantity($newQuantity);
            } else {
                $cartItem = new CartItem();
                $cartItem->setCart($cart);
                $cartItem->setProduct($product);
                $cartItem->setQuantity($quantity);
                $entityManager->persist($cartItem);
            }
        }

        $cart->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'cartCount' => $cart->getTotalItems(),
            'cartTotal' => $cart->getTotalPrice()
        ]);
    }

    #[Route('/api/{_locale}/cart/update/{id}', name: 'api.cart.update', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function apiUpdateCart(
        int $id,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $cartItem = $cartItemRepository->find($id);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Article non trouvé'], 404);
        }

        $action = $request->request->get('action');

        if ($action === 'increase') {
            if ($cartItem->getQuantity() < $cartItem->getProduct()->getStock()) {
                $cartItem->setQuantity($cartItem->getQuantity() + 1);
                $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
                $entityManager->flush();
            } else {
                return new JsonResponse(['error' => 'Stock insuffisant'], 400);
            }
        } elseif ($action === 'decrease') {
            if ($cartItem->getQuantity() > 1) {
                $cartItem->setQuantity($cartItem->getQuantity() - 1);
                $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
                $entityManager->flush();
            } else {
                return new JsonResponse(['error' => 'Quantité minimum atteinte'], 400);
            }
        } else {
            return new JsonResponse(['error' => 'Action invalide'], 400);
        }

        $cart = $cartItem->getCart();

        return new JsonResponse([
            'success' => true,
            'message' => 'Quantité mise à jour',
            'cartCount' => $cart->getTotalItems(),
            'cartTotal' => $cart->getTotalPrice(),
            'itemQuantity' => $cartItem->getQuantity(),
            'itemTotal' => $cartItem->getTotalPrice()
        ]);
    }

    #[Route('/api/{_locale}/cart/remove/{id}', name: 'api.cart.remove', requirements: ['_locale' => 'fr|en'], methods: ['POST'])]
    public function apiRemoveFromCart(
        int $id,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $cartItem = $cartItemRepository->find($id);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Article non trouvé'], 404);
        }

        $cart = $cartItem->getCart();
        $entityManager->remove($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Article retiré du panier',
            'cartCount' => $cart->getTotalItems(),
            'cartTotal' => $cart->getTotalPrice()
        ]);
    }

    #[Route('/api/{_locale}/cart/count', name: 'api.cart.count', requirements: ['_locale' => 'fr|en'], methods: ['GET'])]
    public function apiGetCartCount(CartRepository $cartRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['count' => 0, 'total' => 0]);
        }

        $cart = $cartRepository->findActiveCartByUser($user);
        if (!$cart) {
            return new JsonResponse(['count' => 0, 'total' => 0]);
        }

        return new JsonResponse([
            'count' => $cart->getTotalItems(),
            'total' => $cart->getTotalPrice()
        ]);
    }

}
