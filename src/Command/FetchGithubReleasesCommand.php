<?php

namespace App\Command;

use App\Entity\Release;
use App\Entity\GithubRepository;
use App\Repository\ReleaseRepository;
use App\Repository\GithubRepositoryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:fetch-github-releases',
    description: 'Récupère les dernières releases des dépôts GitHub et les enregistre en base de données.',
)]
class FetchGithubReleasesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private GithubRepositoryRepository $githubRepositoryRepository;
    private ReleaseRepository $releaseRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient,
        GithubRepositoryRepository $githubRepositoryRepository,
        ReleaseRepository $releaseRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->githubRepositoryRepository = $githubRepositoryRepository;
        $this->releaseRepository = $releaseRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositories = $this->githubRepositoryRepository->findAll();

        foreach ($repositories as $repository) {
            $feedUrl = $repository->getReleaseFeedUrl();

            try {
                $response = $this->httpClient->request('GET', $feedUrl);
                $content = $response->getContent();

                $xml = simplexml_load_string($content);

                if ($xml && isset($xml->entry)) {
                    foreach ($xml->entry as $entry) {
                        // Vérifie si la release existe déjà
                        $existingRelease = $this->releaseRepository->findOneBy([
                            'url' => (string)$entry->link['href'],
                            'githubRepo' => $repository
                        ]);

                        if (!$existingRelease) {
                            $release = new Release();
                            $release->setTitle((string)$entry->title);
                            $release->setContent((string)$entry->content);
                            $release->setUpdated(new \DateTime((string)$entry->updated));
                            $release->setUrl((string)$entry->link['href']);
                            $release->setGithubRepo($repository);

                            $this->entityManager->persist($release);

                            // Met à jour la dernière release du repo si c'est la première entrée
                            if ($entry === $xml->entry[0]) {
                                $repository->setLastRelease((string)$entry->title);
                            }

                            $output->writeln(sprintf(
                                'Nouvelle release ajoutée pour %s/%s: %s',
                                $repository->getOwner(),
                                $repository->getName(),
                                $release->getTitle()
                            ));
                        }
                    }

                    $this->entityManager->flush();
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf(
                    'Erreur lors du traitement du dépôt %s/%s: %s',
                    $repository->getOwner(),
                    $repository->getName(),
                    $e->getMessage()
                ));
            }
        }

        $output->writeln('Traitement des releases GitHub terminé.');

        return Command::SUCCESS;
    }
}
