<?php

namespace App\Controller\Admin;

use App\Entity\AcademicProgram;
use App\Entity\Article;
use App\Entity\Establishment;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function configureAssets(): Assets
    {
        // Inclusion de 'app' pour les controllers Stimulus dansn EAB
        return Assets::new()
            ->addCssFile('styles/app.css')
            ->addAssetMapperEntry('app');
    }
    
    public function index(): Response {

    return $this->render('bundles/EasyAdminBundle/page/dashboard.html.twig', [
            'usersCount' => $this->entityManager->getRepository(User::class)->count([]),
            'articlesCount' => $this->entityManager->getRepository(Article::class)->count([]),
            'eventsCount' => $this->entityManager->getRepository(Event::class)->count([]),
            'establishmentsCount' => $this->entityManager->getRepository(Establishment::class)->count([]),
            'academicProgramCount' => $this->entityManager->getRepository(AcademicProgram::class)->count([]),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du CN2E');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(
            'admin.dashboard.label',
            'fa fa-home'
        );

        if ($this->isGranted('ROLE_CN2E_ADMIN')) {
            
            yield MenuItem::linkToRoute(
                'admin.user.plural',
                'fa fa-users',
                'admin_user_index'
            );
        
        }

        if ($this->isGranted('ROLE_CN2E_ADMIN')) {

            // todo : route d'utilisateurs en attente

            yield MenuItem::linkToRoute(
                'admin.article.plural',
                'fa fa-newspaper',
                'admin_article_index'
            );

            yield MenuItem::linkToRoute(
                'admin.event.plural',
                'fa fa-calendar',
                'admin_event_index'
            );
        }

        if (
            $this->isGranted('ROLE_LOCAL_ADMIN')
            || $this->isGranted('ROLE_CN2E_ADMIN')
        ) {
            yield MenuItem::linkToRoute(
                'admin.establishment.plural',
                'fa fa-school',
                'admin_establishment_index'
            );

            yield MenuItem::linkToRoute(
                'admin.academicprogram.plural',
                'fa fa-graduation-cap',
                'admin_academic_program_index'
            );
        }
    }
}