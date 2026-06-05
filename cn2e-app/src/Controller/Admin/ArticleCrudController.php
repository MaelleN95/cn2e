<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use App\Entity\Document;
use App\Form\DocumentType;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleCrudController extends AbstractCrudController
{

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

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::INDEX, 'ROLE_CN2E_ADMIN')
            ->setPermission(Action::NEW, 'ARTICLE_CREATE')
            ->setPermission(Action::EDIT, 'ARTICLE_EDIT')
            ->setPermission(Action::DELETE, 'ARTICLE_DELETE');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'admin.article.title');

        yield DateField::new('publishedAt', 'admin.article.publishedAt')
            ->hideOnForm();

        yield TextField::new('category', 'admin.article.category')
            ->onlyOnForms();

        yield TextareaField::new('shortDescription', 'admin.article.shortDescription');

        yield TextEditorField::new('content', 'admin.article.content')
            ->onlyOnForms();

        yield Field::new('imageFile', 'admin.article.imageFile')
            ->setFormType(VichImageType::class)
            ->onlyOnForms()
            ->setFormTypeOptions([
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'attr' => [
                    'data-controller' => 'image-upload-preview',
                    'data-action' => 'change->image-upload-preview#onChange',
                ],
            ]);
            dump($this->getContext()->getEntity()->getFields());

            dd($this->getContext()->getEntity()->getInstance()->getDocuments());

        yield CollectionField::new('documents', 'admin.article.documents')
            ->onlyOnForms()
            ->setEntryType(DocumentType::class);

        yield BooleanField::new('isMembersOnly', 'admin.article.isMembersOnly');


        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield AssociationField::new('author', 'admin.article.author')
                ->hideOnForm();
        }
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

        // SUPER ADMIN voit tout
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return $qb;
        }

        // CN2E ADMIN voit uniquement ses articles
        $qb
            ->andWhere('entity.author = :user')
            ->setParameter('user', $this->getUser());

        return $qb;
    }
}
