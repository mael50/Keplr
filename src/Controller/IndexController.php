<?php

namespace App\Controller;

use App\Form\ToolType;
use App\Form\RSSFeedType;
use App\Repository\ToolRepository;
use App\Repository\ArticleRepository;
use App\Repository\RSSFeedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    private $toolRepository;
    private $rssFeedRepository;
    private $articleRepository;

    public function __construct(ToolRepository $toolRepository, RSSFeedRepository $rssFeedRepository, ArticleRepository $articleRepository)
    {
        $this->toolRepository = $toolRepository;
        $this->rssFeedRepository = $rssFeedRepository;
        $this->articleRepository = $articleRepository;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $tools = $this->toolRepository->findAll();
        $rssFeeds = $this->rssFeedRepository->findAll();

        $articles = [];
        $dates = [];
        foreach ($rssFeeds as $rssFeed) {
            $feedArticles = $this->articleRepository->findByRssFeed($rssFeed);
            foreach ($feedArticles as $article) {
                $date = $article->getPubDate()->format('Y-m-d');
                $articles[$date][$rssFeed->getName()][] = $article;
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
            }
        }
        rsort($dates);

        // FORMULAIRE POUR LES OUTILS
        $toolForm = $this->createForm(ToolType::class);
        $toolForm->handleRequest($request);
        $data = $toolForm->getData();

        if ($toolForm->isSubmitted() && $toolForm->isValid()) {
            return $this->redirectToRoute('app_tool_add', [
                'url' => $data['url'],
            ]);
        }

        // FORMULAIRE POUR LES FLUX RSS
        $rssFeedForm = $this->createForm(RSSFeedType::class);
        $rssFeedForm->handleRequest($request);

        if ($rssFeedForm->isSubmitted() && $rssFeedForm->isValid()) {
            $rssFeed = $rssFeedForm->getData();
            return $this->redirectToRoute('app_rss_add', [
                'url' => $rssFeed->getUrl(),
                'name' => $rssFeed->getName(),
            ]);
        }


        return $this->render('index/index.html.twig', [
            'toolForm' => $toolForm->createView(),
            'rssFeedForm' => $rssFeedForm->createView(),
            'tools' => $tools,
            'rssFeeds' => $rssFeeds,
            'articles' => $articles,
        ]);
    }
}
