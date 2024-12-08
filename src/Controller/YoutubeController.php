<?php

namespace App\Controller;

use App\Entity\YoutubeChannel;
use App\Service\YoutubeService;
use App\Form\YoutubeChannelType;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\YoutubeChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class YoutubeController extends AbstractController
{
    private $em;
    private $youtubeService;
    private $youtubeChannelRepository;

    public function __construct(EntityManagerInterface $em, YoutubeService $youtubeService, YoutubeChannelRepository $youtubeChannelRepository)
    {
        $this->em = $em;
        $this->youtubeService = $youtubeService;
        $this->youtubeChannelRepository = $youtubeChannelRepository;
    }

    #[Route('/youtube/list', name: 'app_youtube_list')]
    public function listChannels(YoutubeChannelRepository $youtubeChannelRepository): Response
    {
        $channels = $youtubeChannelRepository->findBy(['user' => $this->getUser()]);

        return $this->render('youtube/list.html.twig', [
            'channels' => $channels,
        ]);
    }

    #[Route('/youtube/add', name: 'app_youtube_channel_add')]
    public function addChannel(Request $request): Response
    {
        $userChannels = $this->youtubeChannelRepository->findBy(['user' => $this->getUser()]);

        if ($request->query->has('url')) {
            $channelExists = false;

            foreach ($userChannels as $channel) {
                if ($channel->getUrl() === $request->query->get('url')) {
                    $channelExists = true;
                    break;
                }
            }

            if ($channelExists) {
                $this->addFlash('error', 'La chaîne YouTube existe déjà.');
                return $this->redirectToRoute('app_youtube_list');
            }

            $channel = new YoutubeChannel();
            $url = $request->query->get('url');

            $channelDetails = $this->youtubeService->getChannelDetails($url);

            $channel->setName($channelDetails['snippet']['title']);
            $channel->setDescription($channelDetails['snippet']['description']);
            $channel->setPhoto($channelDetails['snippet']['thumbnails']['high']['url']);
            $channel->setUrl($url);
            $channel->setChannelId($channelDetails['id']);
            $channel->setUser($this->getUser());

            $this->em->persist($channel);
            $this->em->flush();

            $this->addFlash('success', 'La chaîne YouTube a été ajoutée avec succès.');

            return $this->redirectToRoute('app_youtube_list');
        }

        $this->addFlash('error', 'Une erreur est survenue lors de l\'ajout de la chaîne YouTube.');
        return $this->redirectToRoute('app_home');
    }

    // route for deleting a channel
    #[Route('/youtube/delete/{id}', name: 'app_youtube_channel_delete')]
    public function deleteChannel(YoutubeChannel $channel): Response
    {
        $this->em->remove($channel);
        $this->em->flush();

        $this->addFlash('success', 'La chaîne YouTube a été supprimée avec succès.');

        return $this->redirectToRoute('app_youtube_list');
    }

    #[Route('/youtube/edit/{id}', name: 'app_youtube_channel_edit')]
    public function editChannel(Request $request, int $id): Response
    {
        $channel = $this->em->getRepository(YoutubeChannel::class)->find($id);

        if (!$channel) {
            return $this->redirectToRoute('app_youtube_list');
        }

        if ($request->isMethod('POST')) {
            $channel->setName($request->request->get('name'));
            $channel->setUrl($request->request->get('url'));
            $channel->setDescription($request->request->get('description'));
            $channel->setPhoto($request->request->get('photo'));

            $this->em->persist($channel);
            $this->em->flush();

            return $this->redirectToRoute('app_youtube_list');
        }

        return $this->render('youtube/edit.html.twig', [
            'channel' => $channel
        ]);
    }

    #[Route('/youtube/latest-videos', name: 'app_youtube_channel_latest_videos')]
    public function latestVideos(VideoRepository $videoRepository): Response
    {
        $channels = $this->youtubeChannelRepository->findBy(['user' => $this->getUser()]);
        $videos = [];

        foreach ($channels as $channel) {
            $channelVideos = $videoRepository->findBy(
                ['channel' => $channel],
                ['updatedAt' => 'DESC'],
                '9'
            );

            if (!empty($channelVideos)) {
                $videos[$channel->getName()] = $channelVideos;
            }
        }


        return $this->render('youtube/latest_videos.html.twig', [
            'videos' => $videos,
        ]);
    }
}
