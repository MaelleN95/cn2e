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
        return Assets::new()
            ->addCssFile('styles/app.css');
    }
    
    public function index(): Response {

    return $this->render('bundles/EasyAdminBundle/page/dashboard.html.twig', [
            'usersCount' => $this->entityManager->getRepository(User::class)->count([]),
            'articlesCount' => $this->entityManager->getRepository(Article::class)->count([]),
            'eventsCount' => $this->entityManager->getRepository(Event::class)->count([]),
            'establishmentsCount' => $this->entityManager->getRepository(Establishment::class)->count([]),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration CN2E');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Contenu');

        yield MenuItem::linkToRoute('Articles', 'fa fa-newspaper', 'admin_article_index');
        yield MenuItem::linkToRoute('Événements', 'fa fa-calendar', 'admin_event_index');
        yield MenuItem::linkToRoute('Établissements', 'fa fa-school', 'admin_establishment_index');
        yield MenuItem::linkToRoute('Diplômes', 'fa fa-graduation-cap', 'admin_academic_program_index');

        yield MenuItem::section('Utilisateurs');

        yield MenuItem::linkToRoute('Membres', 'fa fa-users', 'admin_user_index');
    }
}