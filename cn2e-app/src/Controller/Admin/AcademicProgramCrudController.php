<?php

namespace App\Controller\Admin;

use App\Entity\AcademicProgram;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AcademicProgramCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AcademicProgram::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('admin.academicprogram.singular')
            ->setEntityLabelInPlural('admin.academicprogram.plural');
    }

    public function configureFields(string $pageName): iterable
    {
        
        yield TextField::new('level', 'admin.academicprogram.level');
        
        yield TextField::new('title', 'admin.academicprogram.title');

        yield AssociationField::new('establishments', 'admin.academicprogram.establishments')
            ->autocomplete()
            ->setHelp('admin.academicprogram.establishmentsHelp')
            ->setFormTypeOption('by_reference', false);
    }
}
