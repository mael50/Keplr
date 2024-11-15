<?php

namespace App\Controller;

use App\Form\ToolType;
use App\Repository\ToolRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends AbstractController
{
    private $toolRepository;

    public function __construct(ToolRepository $toolRepository)
    {
        $this->toolRepository = $toolRepository;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $tools = $this->toolRepository->findAll();

        $form = $this->createForm(ToolType::class);

        $form->handleRequest($request);

        $data = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_tool_add', [
                'url' => $data['url'],
            ]);
        }

        return $this->render('index/index.html.twig', [
            'form' => $form->createView(),
            'tools' => $tools,
        ]);
    }
}
