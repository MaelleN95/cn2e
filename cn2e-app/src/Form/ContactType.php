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
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'form.contact.name_min_length',
                        'maxMessage' => 'form.contact.name_max_length',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[\p{L} \'-]+$/u',
                        'message' => 'Nom invalide',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/https?:\/\/|www\./i',
                        'match' => false,
                        'message' => 'form.contact.no_links',
                    ])
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.contact.email',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length([
                        'min' => 4,
                        'max' => 150,
                        'minMessage' => 'form.contact.email_min_length',
                        'maxMessage' => 'form.contact.email_max_length',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[\x00-\x1F\x7F]/',
                        'match' => false,
                        'message' => 'Caractères invalides',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/https?:\/\/|www\./i',
                        'match' => false,
                        'message' => 'form.contact.no_links',
                    ])
                ],
            ])
            ->add('objectMessage', TextType::class, [
                'label' => 'form.contact.objectMessage',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 4,
                        'max' => 150,
                        'minMessage' => 'form.contact.objectMessage_min_length',
                        'maxMessage' => 'form.contact.objectMessage_max_length',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[\x00-\x1F\x7F]/',
                        'match' => false,
                        'message' => 'Caractères invalides',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/https?:\/\/|www\./i',
                        'match' => false,
                        'message' => 'form.contact.no_links',
                    ])
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'form.contact.message',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 3000,
                        'minMessage' => 'form.contact.message_min_length',
                        'maxMessage' => 'form.contact.message_max_length',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[\x00-\x1F\x7F]/',
                        'match' => false,
                        'message' => 'Caractères invalides',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/https?:\/\/|www\./i',
                        'match' => false,
                        'message' => 'form.contact.no_links',
                    ])
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