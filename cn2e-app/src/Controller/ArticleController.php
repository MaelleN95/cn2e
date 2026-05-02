<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArticleController extends AbstractController
{
    #[Route('/actualites', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/actualites/archives', name: 'app_article_archive')]
    public function archive(): Response
    {
        return $this->render('article_archive/index.html.twig', [
            'controller_name' => 'ArticleArchiveController',
        ]);
    }
}
