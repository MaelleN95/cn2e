<?php

namespace App\Controller\Admin;

use App\Entity\Establishment;
use App\Service\EstablishmentGeocoder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;

class EstablishmentCrudController extends AbstractCrudController
{
    public function __construct(
        private EstablishmentGeocoder $geocoder,
        private Security $security
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

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::NEW, 'ROLE_CN2E_ADMIN')
            ->setPermission(Action::DELETE, 'ESTABLISHMENT_DELETE')
            ->setPermission(Action::EDIT, 'ESTABLISHMENT_EDIT');
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

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // SUPER ADMIN et CN2E ADMIN voient tout
        if ($this->isGranted('ROLE_SUPER_ADMIN') || $this->isGranted('ROLE_CN2E_ADMIN')) {
            return $qb;
        }

        // LOCAL ADMIN voit uniquement son établissement
        $user = $this->security->getUser();

        /** @var \App\Entity\User $user */
        $qb->andWhere('entity = :establishment')
        ->setParameter('establishment', $user->getEstablishment());

        return $qb;
    }
}
