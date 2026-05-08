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
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
                'label' => 'form.registration.last_name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.registration.last_name_not_blank',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'form.registration.last_name_too_long',
                    ]),
                ],
            ])

            ->add('firstName', TextType::class, [
                'label' => 'form.registration.first_name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.registration.first_name_not_blank',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'form.registration.first_name_too_long',
                    ]),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'form.registration.email',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.registration.email_not_blank',
                    ]),
                    new Assert\Email([
                        'message' => 'form.registration.invalid_email',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'form.registration.email_too_long',
                    ]),
                ],
            ])

            ->add('establishment', EntityType::class, [
                'label' => 'form.registration.establishment',
                'class' => Establishment::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez votre établissement',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.registration.establishment_not_blank',
                    ]),
                ],
            ])

            ->add('profession', ChoiceType::class, [
                'label' => 'form.registration.profession',
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
                    new Assert\NotBlank([
                        'message' => 'form.registration.profession_not_blank',
                    ]),
                ],
            ])

            ->add('requestMessage', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'form.registration.request_message',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Précisez votre demande ou votre besoin d\'accès',
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 3000,
                        'maxMessage' => 'form.registration.request_message_too_long',
                    ]),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options'  => [
                    'label' => 'form.registration.password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'form.registration.confirm_password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.registration.password_not_blank',
                    ]),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'form.registration.password_too_short',
                        'max' => 4096,
                        'maxMessage' => 'form.registration.password_too_long',
                    ]),
                    new Assert\NotCompromisedPassword(
                        message: 'form.registration.password_compromised'
                    ),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'form.registration.agree_terms',
                'constraints' => [
                    new Assert\IsTrue(
                        message: 'form.registration.agree_terms'
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