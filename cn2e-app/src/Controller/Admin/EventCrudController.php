<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            ->setEntityLabelInPlural('admin.event.plural');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'admin.event.title');

        yield DateTimeField::new('startDate', 'admin.event.startDate')
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('endDate', 'admin.event.endDate')
            ->setFormat('dd/MM/yyyy HH:mm');

        yield TextField::new('location', 'admin.event.location');

        yield TextField::new('category', 'admin.event.category');

        yield TextareaField::new('shortDescription', 'admin.event.shortDescription')
            ->hideOnIndex();

        yield TextEditorField::new('content', 'admin.event.content')
            ->hideOnIndex();

        yield BooleanField::new('isMembersOnly', 'admin.event.isMembersOnly');

        yield BooleanField::new('hasRegistration', 'admin.event.hasRegistration');
    }
}
