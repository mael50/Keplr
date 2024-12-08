<?php
// src/Controller/AddController.php
namespace App\Controller;

use App\Form\ToolType;
use App\Form\RSSFeedType;
use App\Form\YoutubeChannelType;
use App\Form\GithubRepositoryType;
use App\Repository\ToolRepository;
use App\Repository\RSSFeedRepository;
use App\Repository\YoutubeChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AddController extends AbstractController
{
    #[Route('/add', name: 'app_add')]
    public function index(Request $request): Response
    {
        // FORMULAIRE POUR LES OUTILS
        $toolForm = $this->createForm(ToolType::class);
        $toolForm->handleRequest($request);

        if ($toolForm->isSubmitted() && $toolForm->isValid()) {
            return $this->redirectToRoute('app_tool_add', [
                'url' => $toolForm->getData()['url'],
            ]);
        }

        // FORMULAIRE POUR LES FLUX RSS
        $rssFeedForm = $this->createForm(RSSFeedType::class);
        $rssFeedForm->handleRequest($request);

        if ($rssFeedForm->isSubmitted() && $rssFeedForm->isValid()) {
            return $this->redirectToRoute('app_rss_add', [
                'url' => $rssFeedForm->getData()->getUrl(),
                'name' => $rssFeedForm->getData()->getName(),
            ]);
        }

        // FORMULAIRE POUR LES REPOS GITHUB
        $githubRepoForm = $this->createForm(GithubRepositoryType::class);
        $githubRepoForm->handleRequest($request);

        if ($githubRepoForm->isSubmitted() && $githubRepoForm->isValid()) {
            return $this->redirectToRoute('app_github_repo_add', [
                'url' => $githubRepoForm->getData()->getUrl(),
            ]);
        }

        // FORMULAIRE POUR LES CHAINES YOUTUBE
        $youtubeChannelForm = $this->createForm(YoutubeChannelType::class);
        $youtubeChannelForm->handleRequest($request);

        if ($youtubeChannelForm->isSubmitted() && $youtubeChannelForm->isValid()) {
            return $this->redirectToRoute('app_youtube_channel_add', [
                'url' => $youtubeChannelForm->getData()->getUrl(),
            ]);
        }

        return $this->render('add/index.html.twig', [
            'toolForm' => $toolForm->createView(),
            'rssFeedForm' => $rssFeedForm->createView(),
            'githubRepoForm' => $githubRepoForm->createView(),
            'youtubeChannelForm' => $youtubeChannelForm->createView(),
        ]);
    }

    #[Route('/add/search', name: 'app_add_search')]
    public function search(Request $request, ToolRepository $toolRepository, RSSFeedRepository $rssFeedRepository, YoutubeChannelRepository $youtubeChannelRepository): JsonResponse
    {
        $query = $request->query->get('q');
        $results = [];

        // Recherche dans les outils
        $tools = $toolRepository->findBySearchTerm($query, $this->getUser());
        foreach ($tools as $tool) {
            $results[] = [
                'type' => 'tool',
                'title' => $tool->getName(),
                'url' => $tool->getUrl()
            ];
        }

        // Recherche dans les flux RSS
        $rssFeeds = $rssFeedRepository->findBySearchTerm($query, $this->getUser());
        foreach ($rssFeeds as $feed) {
            $results[] = [
                'type' => 'rss',
                'title' => $feed->getName(),
                'url' => $feed->getUrl()
            ];
        }

        // Recherche dans les chaînes YouTube
        $channels = $youtubeChannelRepository->findBySearchTerm($query, $this->getUser());
        foreach ($channels as $channel) {
            $results[] = [
                'type' => 'youtube',
                'title' => $channel->getName(),
                'url' => $channel->getUrl()
            ];
        }

        return $this->json($results);
    }
}
