<?php

namespace App\Form;

use Dom\Text;
use App\Entity\User;
use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'label' => 'security.registration.email',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'security.registration.assert.not_blank_email',
                    ]),
                    new Assert\Email([
                        'message' => "L'adresse email \"{{ value }}\" n'est pas valide.",
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'L\'email doit contenir au moins {{ limit }} caractères.',
                        'max' => 180,
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 5,
                    'placeholder' => 'security.registration.email_placeholder',
                    'type' => 'email',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'security.registration.first_name',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'security.registration.assert.not_blank_first_name',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'max' => 50,
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'security.registration.first_name_placeholder',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'security.registration.last_name',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'security.registration.assert.not_blank_last_name',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'max' => 50,
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'security.registration.last_name_placeholder',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'security.registration.password',
                ],
                'second_options' => [
                    'label' => 'security.registration.confirm_password',
                ],
                'invalid_message' => 'security.registration.assert.password_mismatch',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
                'attr' => [
                    'class' => 'btn btn-primary mt-3',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
