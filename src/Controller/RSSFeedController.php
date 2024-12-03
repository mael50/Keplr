<?php

namespace App\Controller;

use App\Entity\RSSFeed;
use App\Repository\RSSFeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RSSFeedController extends AbstractController
{
    private $httpClient;
    private $em;
    private $rssFeedRepository;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $em, RSSFeedRepository $rssFeedRepository)
    {
        $this->httpClient = $httpClient;
        $this->em = $em;
        $this->rssFeedRepository = $rssFeedRepository;
    }

    #[Route('/rss', name: 'app_rss')]
    public function index()
    {
        $rssFeeds = $this->rssFeedRepository->findBy(['user' => $this->getUser()]);

        return $this->render('rss/list.html.twig', [
            'rssFeeds' => $rssFeeds,
        ]);
    }

    #[Route('/rss/delete/{id}', name: 'app_rss_delete')]
    public function delete($id)
    {
        $rssFeed = $this->rssFeedRepository->find($id);

        $this->em->remove($rssFeed);
        $this->em->flush();

        return $this->redirectToRoute('app_rss');
    }

    #[Route('/rss/add', name: 'app_rss_add')]
    public function add(Request $request): Response
    {
        if ($request->query->has('url')) {
            $url = $request->query->get('url');
            $response = $this->httpClient->request('GET', $url);
            $content = $response->getContent();
            $rss = simplexml_load_string($content);

            $rssFeed = new RSSFeed();
            $rssFeed->setUrl($url);
            $rssFeed->setUser($this->getUser());

            if ($rss === false || !isset($rss->channel->title)) {
                $rssFeed->setName('Flux RSS sans titre');
            } else {
                $rssFeed->setName((string) $rss->channel->title);
            }
        } else {
            $data = $request->request->all();

            if (!isset($data['url']) || !isset($data['name'])) {
                return new JsonResponse([
                    'error' => 'Missing parameters',
                ], 400);
            }

            $rssFeed = new RSSFeed();
            $rssFeed->setUrl($data['url']);
            $rssFeed->setName($data['name']);
            $rssFeed->setUser($this->getUser());
        }

        $this->em->persist($rssFeed);
        $this->em->flush();

        return $this->redirectToRoute('app_rss');
    }
}
