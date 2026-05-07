<?php

namespace App\Form;

use App\Entity\Establishment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ],
            ])

            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email professionnel',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length(max: 180),
                ],
            ])

            ->add('establishment', EntityType::class, [
                'label' => 'Établissement',
                'class' => Establishment::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez votre établissement',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])

            ->add('profession', ChoiceType::class, [
                'label' => 'Rôle / Fonction',
                'placeholder' => 'Sélectionnez votre rôle',
                'choices' => [
                    'Directeur / Directrice' => 'directeur',
                    'Enseignant(e)' => 'enseignant',
                    'Personnel éducatif' => 'personnel-education',
                    'Personnel administratif' => 'personnel-administratif',
                    'Partenaire institutionnel' => 'partenaire',
                    'Autre' => 'autre',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])

            ->add('requestMessage', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Message (optionnel)',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Précisez votre demande ou votre besoin d\'accès',
                ],
                'constraints' => [
                    new Assert\Length(max: 3000),
                ],
            ])

            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
                // 'constraints' => [
                //     new Assert\NotBlank(),
                //     new Assert\Length(
                //         min: 12,
                //         max: 4096,
                //         minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                //     ),
                // ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J’accepte les conditions d’utilisation',
                'constraints' => [
                    new Assert\IsTrue(
                        message: 'Vous devez accepter les conditions.'
                    ),
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