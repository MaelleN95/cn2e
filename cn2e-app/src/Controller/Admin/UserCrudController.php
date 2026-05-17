<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
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
            ->disable(Action::DELETE)
            ->disable(Action::NEW)
            ->setPermission(Action::EDIT, 'ROLE_CN2E_ADMIN');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addJsFile('controllers/user_form_toggle.js');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('lastName', 'admin.user.lastName');

        yield TextField::new('firstName', 'admin.user.firstName');

        yield TextField::new('profession', 'admin.user.profession');

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {

            yield TelephoneField::new('phone', 'admin.user.phone');
        }

        yield BooleanField::new('isCn2eMember', 'admin.user.isCn2eMember')
            ->onlyOnForms()
            ->addCssClass('js-user-form-cn2e-member');

        yield TextField::new('cn2eRole', 'admin.user.cn2eRole')
            ->onlyOnForms()
            ->addCssClass('js-user-form-cn2e-role');

        yield TextField::new('primaryRole', 'Rôle principal')
            ->onlyOnIndex()
            ->formatValue(function ($value, User $user) {
                return match ($user->getPrimaryRole()) {
                    'ROLE_SUPER_ADMIN' => 'Super Admin',
                    'ROLE_CN2E_ADMIN' => 'Admin CN2E',
                    'ROLE_LOCAL_ADMIN' => 'Admin local',
                    'ROLE_CN2E_MEMBER' => 'Membre CN2E',
                    default => 'Utilisateur',
                };
            });

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {

        yield FormField::addFieldset('Gestion des rôles')
            ->setHelp(
                '
                <ul>
                    <li>
                        <strong>Super administrateur :</strong>
                        accès total à la plateforme et gestion des administrateurs.
                    </li>

                    <li>
                        <strong>Administrateur CN2E :</strong>
                        gestion des contenus, utilisateurs et validations CN2E.
                    </li>

                    <li>
                        <strong>Administrateur local :</strong>
                        gestion locale de son établissement et formations affiliées.
                    </li>

                    <li>
                        <strong>Membre CN2E :</strong>
                        accès aux contenus réservés aux adhérents.
                    </li>
                    
                    <li>
                        <strong>Aucun rôle (utilisateur) :</strong>
                        aucun accès supplémentaire que le public.
                    </li>
                </ul>
                '
            );

            yield ChoiceField::new('roles', 'Rôles')
                ->onlyOnForms() 
                ->setChoices([
                    'Super administrateur' => 'ROLE_SUPER_ADMIN',
                    'Administrateur CN2E' => 'ROLE_CN2E_ADMIN',
                    'Administrateur local' => 'ROLE_LOCAL_ADMIN',
                    'Membre CN2E' => 'ROLE_CN2E_MEMBER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded();

        }

        else {

        yield FormField::addFieldset('Gestion des rôles')
            ->setHelp(
                '
                <ul>
                    <li>
                        <strong>Administrateur local :</strong>
                        gestion locale de son établissement et formations affiliées.
                    </li>

                    <li>
                        <strong>Membre CN2E :</strong>
                        accès aux contenus réservés aux adhérents.
                    </li>

                    <li>
                        <strong>Aucun rôle (utilisateur) :</strong>
                        aucun accès supplémentaire que le public.
                    </li>
                </ul>
                '
            );

            yield ChoiceField::new('roles', 'Rôles')
                ->onlyOnForms()
                ->setChoices([
                    'Administrateur local' => 'ROLE_LOCAL_ADMIN',
                    'Membre CN2E' => 'ROLE_CN2E_MEMBER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded();

        }
    }

    public function persistEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {

        $this->sanitizeRoles($entityInstance);

        parent::persistEntity(
            $entityManager,
            $entityInstance
        );
    }

    public function updateEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {

        $this->sanitizeRoles($entityInstance);

        parent::updateEntity(
            $entityManager,
            $entityInstance
        );
    }

    private function sanitizeRoles(User $user): void
    {
        $roles = $user->getRoles();

        /*
         * Un admin CN2E ne peut gérer
         * que le rôle membre.
         */
        if (
            $this->isGranted('ROLE_CN2E_ADMIN')
            && !$this->isGranted('ROLE_SUPER_ADMIN')
        ) {

            $allowedRoles = [
                'ROLE_CN2E_MEMBER',
                'ROLE_LOCAL_ADMIN',
            ];

            $roles = array_intersect(
                $roles,
                $allowedRoles
            );
        }

        $roles = array_values(array_unique($roles));

        $user->setRoles($roles);
    }
}