<?php

namespace App\Controller;

use App\Entity\RSSFeed;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }


    #[Route('/news', name: 'app_article')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findAll();

        $articlesByRssFeed = [];
        foreach ($articles as $article) {
            $rssFeedName = $article->getRssFeed()->getName();
            if (!isset($articlesByRssFeed[$rssFeedName])) {
                $articlesByRssFeed[$rssFeedName] = [];
            }
            $articlesByRssFeed[$rssFeedName][] = $article;
        }

        return $this->render('article/list.html.twig', [
            'articles' => $articlesByRssFeed,
        ]);
    }

    #[Route('/news/{rssFeed}', name: 'app_article_rss')]
    public function rss(RSSFeed $rssFeed): Response
    {
        $articles = $this->articleRepository->findByRssFeed($rssFeed);

        return $this->render('article/rss.html.twig', [
            'articles' => $articles,
        ]);
    }
}
