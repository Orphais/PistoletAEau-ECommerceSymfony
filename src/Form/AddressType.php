<?php
// src/Form/AddressType.php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La rue est obligatoire.',
                    ]),
                    new Assert\Length([
                        'max' => 200,
                        'maxMessage' => 'La rue ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '123 Rue de la République',
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La ville est obligatoire.',
                    ]),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'La ville ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Paris',
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le code postal est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 5,
                        'exactMessage' => 'Le code postal doit contenir exactement {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d{5}$/',
                        'message' => 'Le code postal doit contenir 5 chiffres.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '75001',
                    'maxlength' => 5,
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le pays est obligatoire.',
                    ]),
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'Le pays ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'France',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}