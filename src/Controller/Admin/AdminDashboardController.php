<?php

namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdminDashboardController extends AbstractController
{
    #[Route('/{_locale}/admin', name: 'admin', requirements: ['_locale' => 'fr|en'])]
    public function index(CategoryRepository $categoryRepository, OrderRepository $orderRepository, ProductRepository $productRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }
        $categories = $categoryRepository->findAll();
        $orders = $orderRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            5
        );
        $ordersDone = $orderRepository->findAll();

        $stockData = $productRepository->getStockRatio();

        $total = array_sum(array_column($stockData, 'count'));

        $ratios = [];
        foreach ($stockData as $data) {
            $ratios[] = [
                'status' => $data['status'],
                'count' => $data['count'],
                'percentage' => $total > 0 ? round(($data['count'] / $total) * 100, 2) : 0
            ];
        }

        $salesByMonth = $orderRepository->getTotalSalesByMonth();

        return $this->render('pages/admin/index.html.twig', [
            'categories' => $categories,
            'orders' => $orders,
            'ordersDone' => $ordersDone,
            'stockRatio' => $ratios,
            'salesByMonth' => $salesByMonth,
        ]);
    }
}
