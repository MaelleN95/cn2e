<?php

namespace App\Controller\Admin;

use App\Entity\Establishment;
use App\Service\AddressGeocoder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EstablishmentCrudController extends AbstractCrudController
{
    public function __construct(
        private AddressGeocoder $geocoder
    ) {}
    
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

        yield TextField::new('address', 'admin.establishment.address')
            ->hideOnIndex()
            ->setFormTypeOption('attr', [
                'data-controller' => 'ban-autocomplete',
                'data-ban-autocomplete-target' => 'input',
                'data-action' => 'input->ban-autocomplete#search'
            ]);

        yield HiddenField::new('city')
            ->setFormTypeOption('attr', [
                'data-ban-autocomplete-target' => 'city'
            ]);

        yield HiddenField::new('department')
            ->setFormTypeOption('attr', [
                'data-ban-autocomplete-target' => 'department'
            ]);

        yield HiddenField::new('region')
            ->setFormTypeOption('attr', [
                'data-ban-autocomplete-target' => 'region'
            ]);

        yield HiddenField::new('latitude')
            ->setFormTypeOption('attr', [
                'data-ban-autocomplete-target' => 'lat'
            ]);

        yield HiddenField::new('longitude')
            ->setFormTypeOption('attr', [
                'data-ban-autocomplete-target' => 'lng'
            ]);

        yield TextField::new('phone', 'admin.establishment.phone');

        yield TextField::new('email', 'admin.establishment.email');

        yield TextField::new('city', 'admin.establishment.city')
            ->onlyOnIndex();

        yield TextField::new('website', 'admin.establishment.website')
            ->hideOnIndex();

        yield TextareaField::new('description', 'admin.establishment.description')
            ->hideOnIndex();

        yield AssociationField::new('academicPrograms', 'admin.establishment.academicprogram')
            ->hideOnIndex()
            ->autocomplete()
            ->setFormTypeOption('by_reference', false);
    }
}
