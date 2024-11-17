<?php

namespace App\Twig\Components;

use App\Entity\RSSFeed;
use App\Repository\ArticleRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('ArticleGrid')]
class ArticleGrid
{
    use DefaultActionTrait;

    private const PER_PAGE = 5;

    #[LiveProp]
    public int $page = 1;

    #[LiveProp(writable: false)]
    public RSSFeed $rssFeed;

    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        $totalArticles = $this->articleRepository->count(['RssFeed' => $this->rssFeed]);
        return $totalArticles > ($this->page * self::PER_PAGE);
    }

    public function getItems(): array
    {
        return $this->articleRepository->findBy(
            ['RssFeed' => $this->rssFeed],
            ['pubDate' => 'DESC'],
            self::PER_PAGE,
            ($this->page - 1) * self::PER_PAGE
        );
    }
}
