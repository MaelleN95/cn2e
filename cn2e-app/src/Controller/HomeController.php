<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\EstablishmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository, EstablishmentRepository $establishmentRepository, UserRepository $userRepository): Response
    {
        $articles = $articleRepository->findLatestPublic(3);
        $establishments = $establishmentRepository->findAll();
        $team = array_slice($userRepository->findCn2eMembers(), 0, 6);

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
            'establishments' => $establishments,
            'team' => $team,
        ]);
    }
}
