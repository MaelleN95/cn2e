<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;

class ArticleCrudController extends AbstractCrudController
{

    public function __construct(
        private Security $security,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('admin.article.singular')
            ->setEntityLabelInPlural('admin.article.plural')
            ->setPageTitle('index', 'admin.article.plural')
            ->setPageTitle('new', 'Ajouter une actualité')
            ->setPageTitle('edit', 'Modifier une actualité');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'admin.article.title');

        yield DateTimeField::new('publishedAt', 'admin.article.publishedAt')
            ->hideOnForm();

        yield TextField::new('category', 'admin.article.category')
            ->onlyOnForms();

        yield TextareaField::new('shortDescription', 'admin.article.shortDescription');

        yield TextEditorField::new('content', 'admin.article.content')
            ->onlyOnForms();

        yield TextField::new('image', 'admin.article.image')
            ->onlyOnForms();

        yield BooleanField::new('isMembersOnly', 'admin.article.isMembersOnly');

        yield AssociationField::new('author', 'admin.article.author')
            ->hideOnForm();
    }

    // Définir l'auteur lors de la création d'une actualité
    public function persistEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {
        if (!$entityInstance instanceof Article) {
            return;
        }

        $entityInstance->setAuthor($this->security->getUser());

        parent::persistEntity($entityManager, $entityInstance);
    }
}
