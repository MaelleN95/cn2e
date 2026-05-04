<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArticleController extends AbstractController
{
    #[Route('/actualites', name: 'app_article')]
    public function index(ArticleRepository $repo): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $repo->findRecentes(),
            'archivesCount' => $repo->countArchivees(),
        ]);
    }

    #[Route('/actualites/archives', name: 'app_article_archive')]
    public function archive(ArticleRepository $repo): Response
    {

        $grouped = $repo->findArchiveesGroupedByYear();

        $years = array_keys($grouped);
        rsort($years);

        return $this->render('article/index_archive.html.twig', [
            'groupedArticlesByYear' => $grouped,
            'years' => $years,
        ]);
    }
}
