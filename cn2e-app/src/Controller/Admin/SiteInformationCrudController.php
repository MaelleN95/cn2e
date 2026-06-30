<?php

namespace App\Controller\Admin;

use App\Entity\SiteInformation;
use App\Repository\SiteInformationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
class SiteInformationCrudController extends AbstractCrudController
{
    public function __construct(
        private SiteInformationRepository $siteInformationRepository,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SiteInformation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Informations du site')
            ->setEntityLabelInPlural('Informations du site')
            ->setPageTitle('index', 'Informations du site')
            ->setPageTitle('new', 'Créer les informations du site')
            ->setPageTitle('edit', 'Modifier les informations du site');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN');
    }

    public function index(AdminContext $context): Response
    {
        $siteInformation = $this->siteInformationRepository->findOneBy([]);

        if ($siteInformation) {
            return $this->redirect(
                $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($siteInformation->getId())
                    ->generateUrl()
            );
        }

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Identité')
            ->setHelp('Ces informations sont réutilisées dans le site, le pied de page et les emails.') ;

        yield TextField::new('organizationName', 'Nom officiel');
        yield TextField::new('acronym', 'Acronyme')
            ->setRequired(false)
            ->hideOnIndex();

        yield FormField::addFieldset('Adresse postale');

        yield TextField::new('postalAddressLine1', 'Ligne 1');
        yield TextField::new('postalAddressLine2', 'Ligne 2')
            ->setRequired(false)
            ->hideOnIndex();
        yield TextField::new('postalCode', 'Code postal');
        yield TextField::new('city', 'Ville');
        yield TextField::new('country', 'Pays');

        yield FormField::addFieldset('Contact');

        yield EmailField::new('contactEmail', 'Email de contact');

        yield EmailField::new('senderEmail', 'Email d\'envoi')
            ->setHelp('Adresse utilisée comme expéditeur des emails automatiques.');

        yield FormField::addFieldset('Réseaux sociaux')
            ->setHelp('Liens affichés dans le pied de page du site.');

        yield TextField::new('linkedinUrl', 'Lien LinkedIn')
            ->setRequired(false)
            ->setHelp('Exemple: https://www.linkedin.com/company/...');

        yield TextField::new('instagramUrl', 'Lien Instagram')
            ->setRequired(false)
            ->setHelp('Exemple: https://www.instagram.com/...');

        yield TextField::new('facebookUrl', 'Lien Facebook')
            ->setRequired(false)
            ->setHelp('Exemple: https://www.facebook.com/...');
    }
}