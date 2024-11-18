<?php
// src/Twig/UnreadArticlesExtension.php
namespace App\Twig;

use App\Repository\ArticleRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UnreadArticlesExtension extends AbstractExtension
{
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_unread_articles', [$this, 'hasUnreadArticles']),
        ];
    }

    public function hasUnreadArticles(): bool
    {
        return $this->articleRepository->count(['isRead' => false]) > 0;
    }
}
