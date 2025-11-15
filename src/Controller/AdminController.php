<?php

namespace App\Controller;

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

        return $this->render('pages/admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
