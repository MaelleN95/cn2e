<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'invalid_message' => 'form.registration.password_mismatch',
            'options' => [
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ],
            'first_options'  => [
                'label' => 'form.registration.password',
            ],
            'second_options' => [
                'label' => 'form.registration.confirm_password',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
