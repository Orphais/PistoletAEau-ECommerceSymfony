<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'admin.common.fields.email',
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
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'admin.common.fields.firstName',
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
                'label' => 'admin.common.fields.lastName',
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
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
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
