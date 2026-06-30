<?php

namespace App\Form;

use App\Entity\Establishment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'form.profile.last_name',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'form.profile.first_name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.profile.email',
            ])
            ->add('establishment', EntityType::class, [
                'label' => 'form.profile.establishment',
                'class' => Establishment::class,
                'choice_label' => 'name',
                'placeholder' => 'form.profile.select_establishment',
                'required' => false,
            ])
            ->add('profession', TextType::class, [
                'label' => 'form.profile.profession',
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.profile.select_profession',
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'form.profile.image',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'attr' => [
                    'accept' => 'image/*',
                    'data-controller' => 'image-upload-preview',
                    'data-action' => 'change->image-upload-preview#onChange',
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
