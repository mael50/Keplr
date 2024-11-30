<?php

namespace App\Controller;

use Embed\Embed;
use App\Entity\Tool;
use App\Entity\Category;
use App\Repository\ToolRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ToolController extends AbstractController
{
    private $httpClient;
    private $em;
    private $toolRepository;
    private $categoryRepository;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $em, ToolRepository $toolRepository, CategoryRepository $categoryRepository)
    {
        $this->httpClient = $httpClient;
        $this->em = $em;
        $this->toolRepository = $toolRepository;
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/tools', name: 'app_tools')]
    public function show(): Response
    {
        $tools = $this->toolRepository->findBy(['user' => $this->getUser()]);

        usort($tools, function ($a, $b) {
            return $b->getId() <=> $a->getId();
        });

        return $this->render('tool/list.html.twig', [
            'tools' => $tools,
        ]);
    }

    #[Route('/tool/add', name: 'app_tool_add')]
    public function add(Request $request): Response
    {
        $url = $request->query->get('url');

        $embed = new Embed();

        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        $domain = parse_url($url, PHP_URL_HOST);

        $existingTools = $this->toolRepository->findAll();
        foreach ($existingTools as $existingTool) {
            $existingDomain = parse_url($existingTool->getUrl(), PHP_URL_HOST);
            if ($domain === $existingDomain) {
                return $this->redirectToRoute('app_home');
            }
        }

        $info = $embed->get($url);

        $existingTool = $this->toolRepository->findOneBy(['url' => $url]);

        if ($existingTool) {
            return $this->redirectToRoute('app_home');
        }

        $tool = new Tool();
        $tool->setUrl($url);
        $tool->setName($info->title);
        $tool->setDescription($info->description);
        $tool->setCover($info->image);
        $tool->setUser($this->getUser());

        $categoryName = $this->getSuggestedCategory($info->description);
        $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);


        if (!$category) {
            $category = new Category();
            $category->setName($categoryName);
            $this->em->persist($category);
        }

        $tool->addCategory($category);

        $this->em->persist($tool);
        $this->em->flush();

        return $this->redirectToRoute('app_tools');
    }

    #[Route('/tool/delete/{id}', name: 'app_tool_delete')]
    public function delete(int $id): Response
    {
        $tool = $this->toolRepository->find($id);

        if (!$tool) {
            return $this->redirectToRoute('app_tools');
        }

        $this->em->remove($tool);
        $this->em->flush();

        return $this->redirectToRoute('app_tools');
    }

    private function getSuggestedCategory(string $description): string
    {
        $apiUrl = 'https://api.anthropic.com/v1/messages';
        $apiKey = $_ENV['ANTHROPIC_API_KEY'];


        $response = $this->httpClient->request('POST', $apiUrl, [
            'headers' => [
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ],
            'json' => [
                'model' => 'claude-3-5-sonnet-20241022',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Suggérez une catégorie en un seul mot pour la description suivante : ' . $description
                    ]
                ],
                'max_tokens' => 10
            ],
        ]);

        $data = $response->toArray();
        return $data['content'][0]['text'];
    }
}
