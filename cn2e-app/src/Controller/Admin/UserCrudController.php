<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('admin.user.singular')
            ->setEntityLabelInPlural('admin.user.plural');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('lastName', 'admin.user.lastName');

        yield TextField::new('firstName', 'admin.user.firstName');

        yield TextField::new('email', 'admin.user.email');

        yield TextField::new('profession', 'admin.user.profession');

        yield TextField::new('phone', 'admin.user.phone');

        yield BooleanField::new('isCn2eMember', 'admin.user.isCn2eMember');

        yield TextField::new('cn2eRole', 'admin.user.cn2eRole');

        yield AssociationField::new('establishment', 'admin.user.establishment')
            ->autocomplete();

        yield ArrayField::new('roles', 'admin.user.roles');

        yield ChoiceField::new('status', 'admin.user.status')
            ->setChoices([
                'En attente' => 'PENDING',
                'Accepté' => 'APPROVED',
                'Refusé' => 'REJECTED',
            ]);

        yield TextField::new('profilePicture', 'admin.user.profilePicture')
            ->hideOnIndex();
    }
}
