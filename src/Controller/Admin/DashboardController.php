<?php

namespace App\Controller\Admin;

use App\Controller\Admin\ToolCrudController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Controller\Admin\ArticleCrudController;
use App\Controller\Admin\RSSFeedCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use App\Controller\Admin\GithubRepositoryCrudController;
use App\Controller\Admin\PushSubscriptionCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ArticleCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Keplr')
            ->setFaviconPath('favicon.svg');
    }

    public function configureMenuItems(): iterable
    {
        // Navigation
        yield MenuItem::linkToUrl('Back to the public website', 'fas fa-arrow-left', '/');

        // Content Management
        yield MenuItem::section('Content');
        yield MenuItem::linkToCrud('Articles', 'fas fa-newspaper', ArticleCrudController::getEntityFqcn());
        yield MenuItem::linkToCrud('Categories', 'fas fa-tags', CategoryCrudController::getEntityFqcn());
        yield MenuItem::linkToCrud('Tools', 'fas fa-tools', ToolCrudController::getEntityFqcn());

        // External Sources
        yield MenuItem::section('External Sources');
        yield MenuItem::linkToCrud('GitHub Repositories', 'fab fa-github', GithubRepositoryCrudController::getEntityFqcn());
        yield MenuItem::linkToCrud('RSS Feeds', 'fas fa-rss', RSSFeedCrudController::getEntityFqcn());

        // User Management
        yield MenuItem::section('Users');
        yield MenuItem::linkToCrud('Users', 'fas fa-users', UserCrudController::getEntityFqcn());
        yield MenuItem::linkToCrud('Push Subscriptions', 'fas fa-bell', PushSubscriptionCrudController::getEntityFqcn());
        yield MenuItem::linkToCrud('Password Reset Requests', 'fas fa-key', ResetPasswordRequestCrudController::getEntityFqcn());
    }
}
