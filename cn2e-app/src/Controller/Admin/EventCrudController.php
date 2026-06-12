<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('admin.event.singular')
            ->setEntityLabelInPlural('admin.event.plural')
            ->setPageTitle('index', 'admin.event.plural')
            ->setPageTitle('new', 'Ajouter un événement')
            ->setPageTitle('edit', 'Modifier un événement');
    }


    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::INDEX, 'ROLE_CN2E_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_CN2E_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_CN2E_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_CN2E_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'admin.event.title');

        yield DateTimeField::new('startDate', 'admin.event.startDate')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnForms();

        yield DateTimeField::new('startDate', 'admin.event.startDate')
            ->setFormat('dd/MM/yyyy')
            ->onlyOnIndex();

        yield DateTimeField::new('endDate', 'admin.event.endDate')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnForms();

        yield TextField::new('location', 'admin.event.location');

        yield TextField::new('category', 'admin.event.category');

        yield TextareaField::new('shortDescription', 'admin.event.shortDescription')
            ->hideOnIndex();

        yield TextEditorField::new('content', 'admin.event.content')
            ->hideOnIndex()
            ->addCssClass('rich-text');

        yield Field::new('imageFile', 'admin.event.imageFile')
            ->setFormType(VichImageType::class)
            ->onlyOnForms()
            ->setFormTypeOptions([
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'attr' => [
                    'accept' => 'image/*',
                    'data-controller' => 'image-upload-preview',
                    'data-action' => 'change->image-upload-preview#onChange',
                ],
            ]);

        yield BooleanField::new('isMembersOnly', 'admin.event.isMembersOnly');

        yield BooleanField::new('hasRegistration', 'admin.event.hasRegistration');
    }
}
