<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\ToolType;
use App\Form\RSSFeedType;
use App\Form\YoutubeChannelType;
use App\Form\GithubRepositoryType;
use App\Repository\ToolRepository;
use App\Repository\VideoRepository;
use App\Repository\ArticleRepository;
use App\Repository\RSSFeedRepository;
use App\Repository\YoutubeChannelRepository;
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
    private $youtubeChannelRepository;
    private $videoRepository;

    public function __construct(
        ToolRepository $toolRepository,
        RSSFeedRepository $rssFeedRepository,
        ArticleRepository $articleRepository,
        GithubRepositoryRepository $githubRepositoryRepository,
        YoutubeChannelRepository $youtubeChannelRepository,
        VideoRepository $videoRepository
    ) {
        $this->toolRepository = $toolRepository;
        $this->rssFeedRepository = $rssFeedRepository;
        $this->articleRepository = $articleRepository;
        $this->githubRepositoryRepository = $githubRepositoryRepository;
        $this->youtubeChannelRepository = $youtubeChannelRepository;
        $this->videoRepository = $videoRepository;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        $tools = $this->toolRepository->findBy(['user' => $user]);
        $rssFeeds = $this->rssFeedRepository->findBy(['user' => $user]);
        $repositories = $this->githubRepositoryRepository->findBy(['user' => $user]);
        $youtubeChannels = $this->youtubeChannelRepository->findBy(['user' => $user]);

        if (empty($tools) && empty($rssFeeds) && empty($repositories) && empty($youtubeChannels)) {
            return $this->redirectToRoute('app_add');
        }

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

        $totalArticles = 0;
        $unreadArticles = 0;
        foreach ($rssFeeds as $rssFeed) {
            $totalArticles += $this->articleRepository->count(['RssFeed' => $rssFeed]);
            $unreadArticles += $this->articleRepository->count(['RssFeed' => $rssFeed, 'isRead' => false]);
        }


        $stats = [
            'total_articles' => $totalArticles,
            'unread_articles' => $unreadArticles,
        ];

        $userChannels = $this->youtubeChannelRepository->findBy(['user' => $user]);
        $latestVideos = [];
        foreach ($userChannels as $channel) {
            $channelVideos = $this->videoRepository->findBy(['channel' => $channel], ['updatedAt' => 'DESC'], 5);
            $latestVideos = array_merge($latestVideos, $channelVideos);
        }
        usort($latestVideos, function ($a, $b) {
            return $b->getUpdatedAt() <=> $a->getUpdatedAt();
        });
        $latestVideos = array_slice($latestVideos, 0, 5);

        return $this->render('index/index.html.twig', [
            'tools' => $tools,
            'rssFeeds' => $rssFeeds,
            'dates' => $dates,
            'repositories' => $repositories,
            'youtubeChannels' => $youtubeChannels,
            'stats' => $stats,
            'latestVideos' => $latestVideos,
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
