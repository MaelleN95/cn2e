<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Event\UserCreatedEvent;
use App\Service\PasswordResetMailer;
use App\Service\UserCreator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;


class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserCreator $userCreator,
        private EventDispatcherInterface $dispatcher,
        private PasswordResetMailer $passwordResetMailer,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('admin.user.singular')
            ->setEntityLabelInPlural('admin.user.plural')
            ->setPageTitle('index', 'Liste des comptes utilisateurs')
            ->setPageTitle('new', 'Ajouter un utilisateur')
            ->setPageTitle('edit', function (User $user) {
                return 'Modifier ' . $user->getFullName();
            });
    }

    #[AdminRoute('reset-password')]
    public function sendResetPassword(
        AdminContext $context
    ): RedirectResponse {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();

        try {
            $this->passwordResetMailer->send($user);

            $this->addFlash(
                'success',
                sprintf(
                    'Un email de réinitialisation a été envoyé à %s.',
                    $user->getEmail()
                )
            );
        } catch (ResetPasswordExceptionInterface) {
            $this->addFlash(
                'warning',
                'Un email de réinitialisation a déjà été envoyé récemment.'
            );
        }

        $referer = $context->getRequest()->headers->get('referer');

        if (!$referer) {
            return $this->redirectToRoute('admin');
        }

        return $this->redirect($referer);
    }

    public function configureActions(Actions $actions): Actions
    {
        $resetPassword = Action::new(
            'sendResetPassword',
            'Réinit. le mot de passe'
        )
            ->linkToCrudAction('sendResetPassword')
            ->setIcon('fa fa-key');

        return $actions
            ->disable(Action::DELETE)
            ->setPermission(Action::EDIT, 'ROLE_CN2E_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->add(Crud::PAGE_INDEX, $resetPassword);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addJsFile('controllers/user_form_toggle.js');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        );

        $qb
            ->andWhere('entity.status != :refused')
            ->setParameter('refused', UserStatus::REFUSED);

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        $isNew = $pageName === Crud::PAGE_NEW;
        
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
                            aucun accès supplémentaire au public.
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
                ->addCssClass('js-user-form-roles')
                ->renderExpanded();

        } else {

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
                            aucun accès supplémentaire au public.
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
                ->addCssClass('js-user-form-roles')
                ->renderExpanded();
        }

        yield TextField::new('cn2eRole', 'admin.user.cn2eRole')
            ->onlyOnForms()
            ->addCssClass('js-user-form-cn2e-role');

        yield FormField::addFieldset('Informations sur l\'utilisateur');

        yield TextField::new('lastName', 'admin.user.lastName');

        yield TextField::new('firstName', 'admin.user.firstName');

        yield EmailField::new('email', 'Email')
            ->setFormTypeOption('disabled', !$isNew);
        
        yield AssociationField::new('establishment', 'Établissement')
            ->setFormTypeOption('disabled', !$isNew);

        yield TextField::new('profession', 'admin.user.profession')
            ->onlyOnForms();

        yield Field::new('imageFile', 'admin.user.imageFile')
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
    }

    public function persistEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {
        $plainPassword = null;

        if ($entityInstance instanceof User) {
            $plainPassword = $this->userCreator->prepare($entityInstance);
        }

        $this->sanitizeRoles($entityInstance);

        parent::persistEntity(
            $entityManager,
            $entityInstance
        );

        if ($entityInstance instanceof User && $plainPassword) {
            $this->dispatcher->dispatch(
                new UserCreatedEvent(
                    $entityInstance,
                    $plainPassword,
                    $this->getUser()
                )
            );

            $this->addFlash(
                'success',
                sprintf(
                    'Le compte de %s %s (%s) a bien été créé. Un email de création a été envoyé.',
                    $entityInstance->getFirstName(),
                    $entityInstance->getLastName(),
                    $entityInstance->getEmail()
                )
            );
        }
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
         * que le rôle membre et local admin
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

        if (!in_array('ROLE_CN2E_MEMBER', $roles, true)) {
            $user->setCn2eRole(null);
        }
    }
}