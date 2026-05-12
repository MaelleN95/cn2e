<?php

namespace App\Form;

use App\EventSubscriber\FormSecuritySubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordRequestFormType extends AbstractType
{
    public function __construct(
        private FormSecuritySubscriber $subscriber
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber($this->subscriber);

        $builder
            ->add('email', EmailType::class, [
                'label' => 'form.reset_password_request.email',
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank(
                        message: 'form.reset_password_request.email_not_blank',
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
        $resolver->setDefaults([]);
    }
}
