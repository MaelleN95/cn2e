<?php

namespace App\Controller\Admin;

use App\Entity\AcademicProgram;
use App\Entity\Article;
use App\Entity\Establishment;
use App\Entity\Event;
use App\Entity\User;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
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
        private UserRepository $userRepository
    ) {
    }

    public function configureAssets(): Assets
    {
        // Inclusion de 'app' pour les controllers Stimulus dansn EAB
        return Assets::new()
            ->addCssFile('build/app.css')
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
        $pendingCount = $this->userRepository->count([
            'status' => UserStatus::PENDING
        ]);

        yield MenuItem::linkToDashboard(
            'admin.dashboard.label',
            'fa fa-home'
        );

        // USERS
        if ($this->isGranted('ROLE_CN2E_ADMIN')) {

            yield MenuItem::section('Utilisateurs');

            yield MenuItem::linkToRoute(
                'Liste des comptes',
                'fa fa-users',
                'admin_user_index'
            );

            yield MenuItem::linkToRoute(
                sprintf('Validation (%d)', $pendingCount),
                'fa fa-user-clock',
                'admin_user_validation'
            );
        }

        // CONTENT
        if ($this->isGranted('ROLE_CN2E_ADMIN')) {

            yield MenuItem::section('Contenu');

            yield MenuItem::linkToRoute(
                'Articles',
                'fa fa-newspaper',
                'admin_article_index'
            );

            yield MenuItem::linkToRoute(
                'Événements',
                'fa fa-calendar',
                'admin_event_index'
            );
        }

        // ORGANIZATION
        if (
            $this->isGranted('ROLE_LOCAL_ADMIN')
            || $this->isGranted('ROLE_CN2E_ADMIN')
        ) {
            yield MenuItem::section('Organisation');

            yield MenuItem::linkToRoute(
                'Établissements',
                'fa fa-school',
                'admin_establishment_index'
            );

            yield MenuItem::linkToRoute(
                'Formations',
                'fa fa-graduation-cap',
                'admin_academic_program_index'
            );
        }
    }
}