<?php

namespace App\Form;

use App\Entity\CreditCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => [
                    'placeholder' => '1234 5678 9012 3456',
                    'maxlength' => 19,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de carte est requis']),
                    new Assert\Length([
                        'min' => 13,
                        'max' => 19,
                        'minMessage' => 'Le numéro de carte doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le numéro de carte ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9\s]+$/',
                        'message' => 'Le numéro de carte ne peut contenir que des chiffres'
                    ])
                ]
            ])
            ->add('expirationDate', DateType::class, [
                'label' => 'Date d\'expiration',
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y') + 15),
                'input' => 'datetime',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date d\'expiration est requise']),
                    new Assert\GreaterThan([
                        'value' => 'now',
                        'message' => 'La carte ne peut pas être expirée'
                    ])
                ]
            ])
            ->add('cvv', TextType::class, [
                'label' => 'CVV',
                'attr' => [
                    'placeholder' => '123',
                    'maxlength' => 3,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le CVV est requis']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 3,
                        'exactMessage' => 'Le CVV doit contenir exactement {{ limit }} chiffres'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]+$/',
                        'message' => 'Le CVV ne peut contenir que des chiffres'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreditCard::class,
        ]);
    }
}
