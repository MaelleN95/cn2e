<?php

namespace App\Controller\Admin;

use App\Entity\AcademicProgram;
use App\EventSubscriber\AcademicProgramFormSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;

class AcademicProgramCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private AcademicProgramFormSubscriber $academicProgramFormSubscriber,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return AcademicProgram::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setFormOptions([
                'data_class' => AcademicProgram::class,
            ])
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('admin.academicprogram.singular')
            ->setEntityLabelInPlural('admin.academicprogram.plural')
            ->setPageTitle('index', 'admin.academicprogram.plural')
            ->setPageTitle('new', 'Ajouter une formation')
            ->setPageTitle('edit', 'Modifier une formation');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('level', 'admin.academicprogram.level');

        yield TextField::new('title', 'admin.academicprogram.title');

        yield ArrayField::new('establishments', 'admin.academicprogram.establishments')
            ->setFormTypeOption('disabled', true);
    }

    // Indispensable pour lier le FormSubscriber custom
    public function createEditFormBuilder($entityInstance, mixed $pageName, $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityInstance, $pageName, $context);

        $formBuilder->addEventSubscriber($this->academicProgramFormSubscriber);

        return $formBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $addEstablishment = Action::new('addEstablishment', 'Ajouter à mon établissement')
            ->linkToCrudAction('addEstablishment')
            ->displayIf(function (AcademicProgram $academicProgram) {

                /** @var \App\Entity\User $user */
                $user = $this->security->getUser();

                return !$academicProgram
                    ->getEstablishments()
                    ->contains($user->getEstablishment());
            });

        $removeEstablishment = Action::new('removeEstablishment', 'Supprimer de mon établissement')
            ->linkToCrudAction('removeEstablishment')
            ->displayIf(function (AcademicProgram $academicProgram) {

                /** @var \App\Entity\User $user */
                $user = $this->security->getUser();

                return $academicProgram
                    ->getEstablishments()
                    ->contains($user->getEstablishment());
            });

        return $actions
            ->add(Crud::PAGE_EDIT, $addEstablishment)
            ->add(Crud::PAGE_EDIT, $removeEstablishment)
            ->setPermission(Action::INDEX, 'ROLE_LOCAL_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_LOCAL_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_LOCAL_ADMIN')
            ->setPermission(Action::DELETE, 'ACADEMIC_PROGRAM_DELETE');
    }

    private function redirectToCurrentAcademicProgram(AcademicProgram $entity): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('edit')
            ->setEntityId($entity->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    #[AdminRoute('add-establishment')]
    public function addEstablishment(AdminContext $adminContext): Response
    {
        $academicProgram = $adminContext->getEntity()->getInstance();
        $user = $this->security->getUser();

        if ($user && $academicProgram) {
            /** @var \App\Entity\User $user */
            $academicProgram->addEstablishment($user->getEstablishment());

            $this->entityManager->flush();

            $this->addFlash('success', 'Formation ajoutée à votre établissement.');
        }

        return $this->redirectToCurrentAcademicProgram($academicProgram);
    }

    #[AdminRoute('remove-establishment')]
    public function removeEstablishment(AdminContext $adminContext): Response
    {
        $academicProgram = $adminContext->getEntity()->getInstance();
        $user = $this->security->getUser();

        if ($user && $academicProgram) {
            /** @var \App\Entity\User $user */
            $academicProgram->removeEstablishment($user->getEstablishment());

            $this->entityManager->flush();

            $this->addFlash('success', 'Formation supprimée de votre établissement.');
        }

        return $this->redirectToCurrentAcademicProgram($academicProgram);
    }
}