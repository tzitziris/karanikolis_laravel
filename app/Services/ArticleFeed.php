<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ArticleFeed
{
    public const HOME_LIMIT = 3;

    public const NEWS_PAGE_SIZE = 9;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latestForHome(): array
    {
        return $this->publishedCardsQuery()
            ->limit(self::HOME_LIMIT)
            ->get()
            ->map(fn (Article $article): array => $this->cardData($article))
            ->all();
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function archivePage(int $page): LengthAwarePaginator
    {
        $page = max(1, $page);

        return $this->publishedCardsQuery()
            ->paginate(self::NEWS_PAGE_SIZE, ['*'], 'page', $page)
            ->through(fn (Article $article): array => $this->cardData($article));
    }

    /**
     * @return Builder<Article>
     */
    private function publishedCardsQuery(): Builder
    {
        return Article::query()
            ->readyForPublic()
            ->publicationOrder()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'cover_image_name',
                'cover_image_width',
                'cover_image_height',
                'published_at',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function cardData(Article $article): array
    {
        return [
            'coverImageHeight' => $article->cover_image_height,
            'coverImageName' => $article->cover_image_name,
            'coverImageWidth' => $article->cover_image_width,
            'date' => $this->dateForGreekReader($article->published_at),
            'excerpt' => $article->excerpt,
            'href' => "/news/{$article->slug}",
            'id' => $article->id,
            'publishedAt' => $article->published_at?->toISOString(),
            'slug' => $article->slug,
            'title' => $article->title,
        ];
    }

    private function dateForGreekReader(?Carbon $publishedAt): ?string
    {
        return $publishedAt?->locale('el')->translatedFormat('j F Y');
    }
}
