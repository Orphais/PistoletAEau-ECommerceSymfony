<?php

namespace App\Twig\Components;

use App\Entity\CreditCard;
use App\Form\CreditCardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CreditCardForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public array $creditCards = [];

    #[LiveProp]
    public bool $showSuccessMessage = false;

    #[LiveProp]
    public string $successMessage = '';

    protected function instantiateForm(): FormInterface
    {
        $creditCard = new CreditCard();
        $creditCard->setUser($this->getUser());

        return $this->createForm(CreditCardType::class, $creditCard);
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager): void
    {
        $this->submitForm();

        $creditCard = $this->getForm()->getData();

        if (!$this->getForm()->isValid()) {
            return;
        }

        $creditCard->setUser($this->getUser());

        $entityManager->persist($creditCard);
        $entityManager->flush();

        $this->creditCards[] = [
            'number' => $this->maskCardNumber($creditCard->getNumber()),
            'expirationDate' => $creditCard->getExpirationDate()->format('m/Y'),
            'id' => $creditCard->getId()
        ];

        $this->showSuccessMessage = true;
        $this->successMessage = 'Carte bancaire ajoutée avec succès!';

        $this->resetForm();
    }

    #[LiveAction]
    public function addAnother(): void
    {
        $this->resetForm();
        $this->showSuccessMessage = false;
    }

    private function maskCardNumber(string $number): string
    {
        $cleaned = preg_replace('/\s+/', '', $number);
        return '**** **** **** ' . substr($cleaned, -4);
    }
}
