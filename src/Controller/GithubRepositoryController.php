<?php

namespace App\Controller;

use App\Entity\Release;
use App\Entity\GithubRepository;
use App\Repository\ReleaseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GithubRepositoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GithubRepositoryController extends AbstractController
{
    private $githubRepositoryRepository;
    private $releaseRepository;

    public function __construct(GithubRepositoryRepository $githubRepositoryRepository, ReleaseRepository $releaseRepository)
    {
        $this->githubRepositoryRepository = $githubRepositoryRepository;
        $this->releaseRepository = $releaseRepository;
    }

    #[Route('/releases/latest', name: 'app_github_repos')]
    public function show(): Response
    {
        $repositories = $this->githubRepositoryRepository->findBy(['user' => $this->getUser()]);

        return $this->render('github_repository/list.html.twig', [
            'repositories' => $repositories,
        ]);
    }

    #[Route('/githubrepo/add', name: 'app_github_repo_add')]
    public function add(Request $request): Response
    {
        $url = $request->query->get('url');

        if (!preg_match('/^https:\/\/github.com\/[a-zA-Z0-9-]+\/[a-zA-Z0-9-]+$/', $url)) {
            $this->addFlash('error', 'Invalid GitHub repository URL');
            return $this->redirectToRoute('app_home');
        }

        $parts = explode('/', $url);
        $owner = $parts[3];
        $name = $parts[4];

        $existingRepository = $this->githubRepositoryRepository->findOneBy([
            'owner' => $owner,
            'name' => $name,
        ]);

        if ($existingRepository) {
            $this->addFlash('error', 'Repository already exists');
            return $this->redirectToRoute('app_home');
        }

        $repository = new GithubRepository();

        $repository->setOwner($owner);
        $repository->setName($name);
        $repository->setUrl($url);
        $repository->setUser($this->getUser());

        $feedUrl = $url . '/releases.atom';

        $repository->setReleaseFeedUrl($feedUrl);

        $xml = simplexml_load_file($feedUrl);
        $lastRelease = $xml->entry[0];
        $release = new Release();

        $release->setTitle($lastRelease->title);
        $release->setContent($lastRelease->content);
        $release->setUpdated(new \DateTime($lastRelease->updated));
        $release->setUrl($lastRelease->link['href']);
        $release->setGithubRepo($repository);


        $repository->addRelease($release);


        $repository->setLastRelease($lastRelease->title);

        $this->githubRepositoryRepository->save($repository);
        $this->releaseRepository->save($release);

        $this->addFlash('success', 'Repository added');

        return $this->redirectToRoute('app_github_repos');
    }
}
