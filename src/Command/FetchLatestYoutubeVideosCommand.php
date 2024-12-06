<?php
// src/Command/FetchLatestYoutubeVideosCommand.php

namespace App\Command;

use App\Entity\Video;
use App\Service\YoutubeService;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\YoutubeChannelRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-youtube-videos',
    description: 'Récupère les dernières vidéos YouTube de tous les channels',
)]
class FetchLatestYoutubeVideosCommand extends Command
{
    protected static $defaultName = 'app:fetch-youtube-videos';

    public function __construct(
        private YoutubeChannelRepository $channelRepository,
        private VideoRepository $videoRepository,
        private YoutubeService $youtubeService,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Récupère les dernières vidéos YouTube de tous les channels');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $channels = $this->channelRepository->findAll();

        foreach ($channels as $channel) {
            $channelId = $channel->getChannelId();
            $videos = $this->youtubeService->getLatestVideos($channelId);

            if (!$videos || empty($videos)) {
                continue;
            }

            $latestVideo = $videos[0];

            $videoExists = $this->videoRepository->findOneBy([
                'videoId' => $latestVideo['id']
            ]);

            if (!$videoExists) {
                $video = new Video();
                $video->setTitle($latestVideo['title']);
                $video->setVideoId($latestVideo['id']);
                $video->setUpdatedAt(new \DateTime($latestVideo['updated']));
                $video->setLink($latestVideo['link']['@attributes']['href']);
                $video->setThumbnail($latestVideo['thumbnail']);
                $video->setChannel($channel);

                $this->em->persist($video);
                $this->em->flush();

                $io->success(sprintf(
                    'Nouvelle vidéo ajoutée pour %s: %s',
                    $channel->getName(),
                    $latestVideo['title']
                ));
            }
        }

        return Command::SUCCESS;
    }
}
