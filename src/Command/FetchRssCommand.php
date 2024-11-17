<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\RSSFeed;
use App\Entity\Article;

#[AsCommand(
    name: 'app:fetch-rss',
    description: 'Récupère uniquement le dernier article des flux RSS et l\'enregistre s\'il n\'existe pas déjà en base de données.',
)]
class FetchRssCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rssFeeds = $this->entityManager->getRepository(RSSFeed::class)->findAll();

        foreach ($rssFeeds as $rssFeed) {
            $url = $rssFeed->getUrl();

            try {
                $response = $this->httpClient->request('GET', $url);
                $content = $response->getContent();

                $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

                $item = $xml->channel->item[0];

                if ($item) {
                    $existingArticle = $this->entityManager->getRepository(Article::class)->findOneBy(['link' => $item->link]);

                    if (!$existingArticle) {
                        $article = new Article();
                        $article->setTitle((string) $item->title);
                        $article->setDescription((string) $item->description);
                        $article->setLink((string) $item->link);
                        $article->setPubDate(new \DateTime((string) $item->pubDate));
                        $article->setRssFeed($rssFeed);
                        $article->setRead(false);

                        $this->entityManager->persist($article);
                        $this->entityManager->flush();

                        $output->writeln('Article ajouté: ' . $article->getTitle());
                    } else {
                        $output->writeln('L\'article existe déjà: ' . $item->title);
                    }
                } else {
                    $output->writeln('Aucun article trouvé dans le flux: ' . $url);
                }
            } catch (\Exception $e) {
                $output->writeln('Erreur lors du traitement du flux RSS: ' . $url . ' - ' . $e->getMessage());
            }
        }

        $output->writeln('Traitement des flux RSS terminé.');

        return Command::SUCCESS;
    }
}
