<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class ArticleController extends AbstractController
{
    #[Route('/actualites', name: 'app_article')]
    public function index(ArticleRepository $repo): Response
    {
        $isMember = false;
        $user = $this->getUser();

        if ($user instanceof UserInterface && method_exists($user, 'isCn2eMember')) {
            $isMember = $user->isCn2eMember();
        }

        return $this->render('article/index.html.twig', [
            'articles' => $repo->findRecentes(),
            'archivesCount' => $repo->countArchivees(),
            'isMember' => $isMember,
        ]);
    }

    #[Route('/actualites/article/{slug}', name: 'app_article_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Article $article): Response
    {

        if ($article->isMembersOnly() && !$this->isGranted('ROLE_CN2E_MEMBER')) {
            return $this->render('article/access_denied_article.html.twig', [
                'article' => $article,
            ]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
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
