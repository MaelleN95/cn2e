<?php

namespace App\Form;

use App\EventSubscriber\FormSecuritySubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function __construct(
        private FormSecuritySubscriber $subscriber
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber($this->subscriber);

        $builder
            ->add('name', TextType::class, [
                'label' => 'form.contact.name',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.contact.email',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('objectMessage', TextType::class, [
                'label' => 'form.contact.objectMessage',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'form.contact.message',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 10]),
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
    }
}