<?php

namespace App\Controller;

use App\Repository\CreditCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'fr|en'])]
#[IsGranted('ROLE_USER')]
final class CreditCardController extends AbstractController
{
    #[Route('/my-cards', name: 'credit_card.index')]
    public function index(CreditCardRepository $creditCardRepository): Response
    {
        $user = $this->getUser();
        $existingCards = $creditCardRepository->findBy(['user' => $user]);

        $formattedCards = array_map(function ($card) {
            return [
                'number' => $this->maskCardNumber($card->getNumber()),
                'expirationDate' => $card->getExpirationDate()->format('m/Y'),
                'id' => $card->getId()
            ];
        }, $existingCards);

        return $this->render('credit_card/index.html.twig', [
            'existingCards' => $formattedCards,
        ]);
    }

    private function maskCardNumber(string $number): string
    {
        $cleaned = preg_replace('/\s+/', '', $number);
        return '**** **** **** ' . substr($cleaned, -4);
    }
}
