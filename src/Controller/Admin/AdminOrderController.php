<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdminOrderController extends AbstractController
{
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
