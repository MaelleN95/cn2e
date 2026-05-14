<?php

namespace App\Controller\Admin;

use App\Entity\Establishment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EstablishmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Establishment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('admin.establishment.singular')
            ->setEntityLabelInPlural('admin.establishment.plural');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'admin.establishment.name');

        yield TextField::new('city', 'admin.establishment.city');

        yield TextField::new('department', 'admin.establishment.department');

        yield TextField::new('region', 'admin.establishment.region');

        yield TextareaField::new('address', 'admin.establishment.address');

        yield TextField::new('phone', 'admin.establishment.phone');

        yield TextField::new('email', 'admin.establishment.email');

        yield TextField::new('website', 'admin.establishment.website');

        yield TextareaField::new('description', 'admin.establishment.description')
            ->hideOnIndex();

        yield CollectionField::new('academicPrograms')
            ->hideOnIndex();
    }
}
