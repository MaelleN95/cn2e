<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Form\Type\VichFileType;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        dump('DocumentType loaded');

        $builder->add('pdfFile', FileType::class, [
            'label' => 'admin.article.document_file',
            'required' => false,
            'attr' => [
                'accept' => 'application/pdf',
            ],
            'constraints' => [
                new Assert\File([
                    'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                    'mimeTypesMessage' => 'document.pdf.invalid',
                ]),
            ],
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function ($event) {
    dump('PRE_SET_DATA', $event->getData());
});
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
