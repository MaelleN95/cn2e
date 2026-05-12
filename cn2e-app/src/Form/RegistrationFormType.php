<?php

namespace App\Form;

use App\Entity\Establishment;
use App\Entity\User;
use App\EventSubscriber\FormSecuritySubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class RegistrationFormType extends AbstractType
{
    public function __construct(
        private FormSecuritySubscriber $subscriber
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber($this->subscriber);

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
                'placeholder' => 'form.registration.select_establishement',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.registration.establishment_not_blank',
                    ]),
                ],
            ])

            ->add('profession', ChoiceType::class, [
                'label' => 'form.registration.profession',
                'placeholder' => 'form.registration.select_profession',
                'choices' => [
                    'form.registration.profession_director' => 'director',
                    'form.registration.profession_teacher' => 'teacher',
                    'form.registration.profession_educational_staff' => 'educational_staff',
                    'form.registration.profession_administrative_staff' => 'administrative_staff',
                    'form.registration.profession_institutional_partner' => 'institutional_partner',
                    'form.registration.profession_other' => 'other',
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
                    'placeholder' => 'form.registration.request_message_placeholder',
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
                'invalid_message' => 'form.registration.password_mismatch',
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
            // Champ caché anti-spam
            ->add('website', HiddenType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'tabindex' => '-1',
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}