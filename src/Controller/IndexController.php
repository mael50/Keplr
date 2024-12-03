<?php

namespace App\Controller;

use App\Form\ToolType;
use App\Form\RSSFeedType;
use App\Form\GithubRepositoryType;
use App\Repository\ToolRepository;
use App\Repository\ArticleRepository;
use App\Repository\RSSFeedRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GithubRepositoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    private $toolRepository;
    private $rssFeedRepository;
    private $articleRepository;
    private $githubRepositoryRepository;

    public function __construct(
        ToolRepository $toolRepository,
        RSSFeedRepository $rssFeedRepository,
        ArticleRepository $articleRepository,
        GithubRepositoryRepository $githubRepositoryRepository
    ) {
        $this->toolRepository = $toolRepository;
        $this->rssFeedRepository = $rssFeedRepository;
        $this->articleRepository = $articleRepository;
        $this->githubRepositoryRepository = $githubRepositoryRepository;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $tools = $this->toolRepository->findAll();
        $rssFeeds = $this->rssFeedRepository->findAll();
        $repositories = $this->githubRepositoryRepository->findAll();

        $articles = [];
        $dates = [];
        foreach ($rssFeeds as $rssFeed) {
            $feedArticles = $this->articleRepository->findByRssFeed($rssFeed);
            foreach ($feedArticles as $article) {
                $date = $article->getPubDate()->format('Y-m-d');
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

        // FORMULAIRE POUR LES REPOS GITHUB
        $githubRepoForm = $this->createForm(GithubRepositoryType::class);
        $githubRepoForm->handleRequest($request);

        if ($githubRepoForm->isSubmitted() && $githubRepoForm->isValid()) {
            $githubRepo = $githubRepoForm->getData();
            return $this->redirectToRoute('app_github_repo_add', [
                'url' => $githubRepo->getUrl(),
            ]);
        }


        return $this->render('index/index.html.twig', [
            'toolForm' => $toolForm->createView(),
            'rssFeedForm' => $rssFeedForm->createView(),
            'githubRepoForm' => $githubRepoForm->createView(),
            'tools' => $tools,
            'rssFeeds' => $rssFeeds,
            'dates' => $dates,
            'repositories' => $repositories,
        ]);
    }

    #[Route('/api/user-status', name: 'api_user_status')]
    public function userStatus(): JsonResponse
    {
        return new JsonResponse([
            'isLoggedIn' => $this->getUser() !== null
        ]);
    }

    #[Route('/api/articles/{date}', name: 'api_articles_by_date')]
    public function getArticlesByDate(string $date): JsonResponse
    {
        $rssFeeds = $this->rssFeedRepository->findAll();
        $articles = [];

        foreach ($rssFeeds as $rssFeed) {
            $feedArticles = $this->articleRepository->findByRssFeed($rssFeed);
            foreach ($feedArticles as $article) {
                if ($article->getPubDate()->format('Y-m-d') === $date) {
                    $description = $article->getDescription();
                    $description = strip_tags($description);
                    $description = substr($description, 0, 100) . '...';
                    $articles[$rssFeed->getName()][] = [
                        'id' => $article->getId(),
                        'title' => $article->getTitle(),
                        'link' => $article->getLink(),
                        'pubDate' => $article->getPubDate()->format('H:i'),
                        'isRead' => $article->isRead(),
                        'description' => $description,
                    ];
                }
            }
        }

        return new JsonResponse($articles);
    }
}
