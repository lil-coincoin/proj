<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'Adresse email',
                'constraints' => [
                    new NotBlank([
                        'message' => "L'adresse email est requise"
                    ]),
                    new Email([
                        'message' => "L'adresse email est invalide"
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                //'help' => 'Le mot de passe doit contenir 6 caractères au minimum',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe est requis',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir {{ limit }} caractères minimum',
                        'max' => 4096, // max length allowed by Symfony for security reasons
                    ]),
                ],
                'always_empty' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('username', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Un pseudo est requis'
                    ]),
                    new Length([
                        'max' => 30,
                        'maxMessage' => 'Votre pseudo ne peut dépasser les {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('profile_picture', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Photo de profil',
                'help' => 'Votre photo de profil ne doit pas dépasser les 1Mo et doit être un type : PNG, WEBP ou JPG',
                'constraints' => [
                    new File([
                        'extensions' => ['png', 'jpeg', 'jpg', 'webp'],
                        'extensionsMessage' => "Votre fichier n'est pas une image acceptée",
                        'maxSize' => '1M',
                        'maxSizeMessage' => "L'image ne doit pas dépasser {{ limit }} en poids"
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            // ->add('created_at', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('updated_at', null, [
            //     'widget' => 'single_text',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
