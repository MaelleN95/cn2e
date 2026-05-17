<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(UserRole::CN2E_ADMIN->value)]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs');
    }

    public function configureActions(Actions $actions): Actions
    {
        // $resetPassword = Action::new(
        //     'resetPassword',
        //     'Réinitialiser le mot de passe'
        // );

        return $actions
            ->setPermission(
                Action::DELETE,
                UserRole::SUPER_ADMIN->value
            )
            ->setPermission(
                Action::NEW,
                UserRole::SUPER_ADMIN->value
            );
            // ->add(
            //     Crud::PAGE_EDIT,
            //     $resetPassword
            // );
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('lastName', 'admin.user.lastName');

        yield TextField::new('firstName', 'admin.user.firstName');

        yield TextField::new('profession', 'admin.user.profession');

        if ($this->isGranted(UserRole::SUPER_ADMIN->value)) {

            yield TelephoneField::new('phone', 'admin.user.phone');
        }

        yield BooleanField::new('isCn2eMember', 'admin.user.isCn2eMember');

        yield TextField::new('cn2eRole', 'admin.user.cn2eRole');

        yield FormField::addFieldset('Gestion des rôles');

        yield FormField::addFieldset('')
            ->setHelp(
                '
                <ul>
                    <li>
                        <strong>Super administrateur :</strong>
                        accès complet à la plateforme.
                    </li>

                    <li>
                        <strong>Administrateur CN2E :</strong>
                        gestion des contenus et utilisateurs.
                    </li>

                    <li>
                        <strong>Membre CN2E :</strong>
                        accès aux contenus privés.
                    </li>
                </ul>
                '
            );

        if ($this->isGranted(UserRole::SUPER_ADMIN->value)) {

            yield ChoiceField::new('roles', 'Rôles')
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setChoices(
                    UserRole::choicesForSuperAdmin()
                );
        }

        else {

            yield ChoiceField::new('roles', 'Rôles')
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setChoices(
                    UserRole::choicesForCn2eAdmin()
                );
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
            $this->isGranted(UserRole::CN2E_ADMIN->value)
            && !$this->isGranted(UserRole::SUPER_ADMIN->value)
        ) {

            $allowedRoles = [
                UserRole::CN2E_MEMBER->value,
                UserRole::LOCAL_ADMIN->value,
            ];

            $roles = array_intersect(
                $roles,
                $allowedRoles
            );
        }

        /*
         * On force toujours le rôle local admin
         * si l'utilisateur possède un établissement.
         */
        if (
            $user->getEstablishment()
            && !in_array(
                UserRole::LOCAL_ADMIN->value,
                $roles,
                true
            )
        ) {

            $roles[] = UserRole::LOCAL_ADMIN->value;
        }

        $user->setRoles(
            array_unique($roles)
        );
    }
}