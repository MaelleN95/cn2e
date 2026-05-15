<?php

namespace App\Controller\Admin;

use App\Entity\Establishment;
use App\Service\EstablishmentGeocoder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EstablishmentCrudController extends AbstractCrudController
{
    public function __construct(
        private EstablishmentGeocoder $geocoder
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
                'autocomplete' => 'none',
                'data-controller' => 'ban-autocomplete',
                'data-ban-autocomplete-target' => 'input',
                'data-action' => 'input->ban-autocomplete#search'
            ]);

        yield TextField::new('city', 'admin.establishment.city')
            ->onlyOnIndex();

        yield TextField::new('phone', 'admin.establishment.phone');
        yield TextField::new('email', 'admin.establishment.email');

        yield TextField::new('website', 'admin.establishment.website')
            ->hideOnIndex();

        yield TextareaField::new('description', 'admin.establishment.description')
            ->hideOnIndex();

        yield AssociationField::new('academicPrograms', 'admin.establishment.academicprogram')
            ->hideOnIndex()
            ->autocomplete();
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Establishment) {
            $this->geocoder->hydrate($entityInstance);
        }

        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Establishment) {
            $this->geocoder->hydrate($entityInstance);
        }

        parent::updateEntity($em, $entityInstance);
    }
}
