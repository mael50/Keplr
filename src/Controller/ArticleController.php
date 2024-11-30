<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\RSSFeed;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    private $entityManager;
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
    }

    #[Route('/news', name: 'app_article')]
    public function index(): Response
    {
        $user = $this->getUser();

        $userRssFeeds = $user->getRSSFeeds();

        $articlesByRssFeed = [];
        foreach ($userRssFeeds as $rssFeed) {
            $articles = $this->articleRepository->findByRssFeed($rssFeed);

            if (!empty($articles)) {
                $articlesByRssFeed[$rssFeed->getName()] = $articles;
            }
        }

        return $this->render('article/list.html.twig', [
            'articles' => $articlesByRssFeed,
        ]);
    }

    #[Route('/news/{rssFeed}', name: 'app_article_rss')]
    public function rss(RSSFeed $rssFeed): Response
    {
        return $this->render('article/rss.html.twig', [
            'rssFeed' => $rssFeed,
        ]);
    }

    #[Route('/news/mark-as-read/{id}', name: 'app_article_mark_as_read', methods: ['POST'])]
    public function markAsRead(Article $article): JsonResponse
    {
        $article->setRead(true);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
