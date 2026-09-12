<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleVideo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ArticleFeed
{
    public const HOME_LIMIT = 3;

    public const NEWS_PAGE_SIZE = 9;

    public function __construct(private readonly UploadedArticleImageService $uploadedImages) {}

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
     * @return array<string, mixed>|null
     */
    public function articlePage(string $slug, ArticleBodyRenderer $renderer): ?array
    {
        $article = Article::query()
            ->readyForPublic()
            ->where('slug', $slug)
            ->with([
                'images' => fn ($query) => $query
                    ->select(['id', 'article_id', 'image_name', 'alt_text', 'width', 'height', 'sort_order'])
                    ->orderBy('sort_order'),
                'videos' => fn ($query) => $query
                    ->select(['id', 'article_id', 'youtube_id', 'sort_order'])
                    ->orderBy('sort_order'),
            ])
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'body',
                'cover_image_name',
                'cover_image_width',
                'cover_image_height',
                'published_at',
            ])
            ->first();

        if (! $article instanceof Article) {
            return null;
        }

        return [
            'bodyHtml' => $renderer->render($article->body),
            'coverImageHeight' => $article->cover_image_height,
            'coverImage' => $this->imageData(
                $article->cover_image_name,
                $article->cover_image_width,
                $article->cover_image_height,
                'cover',
            ),
            'coverImageName' => $article->cover_image_name,
            'coverImageWidth' => $article->cover_image_width,
            'date' => $this->dateForGreekReader($article->published_at),
            'excerpt' => $article->excerpt,
            'gallery' => $article->images
                ->map(fn (ArticleImage $image): array => [
                    'altText' => $image->alt_text,
                    'height' => $image->height,
                    'id' => $image->id,
                    'image' => $this->imageData($image->image_name, $image->width, $image->height, 'gallery'),
                    'imageName' => $image->image_name,
                    'sortOrder' => $image->sort_order,
                    'width' => $image->width,
                ])
                ->all(),
            'id' => $article->id,
            'publishedAt' => $article->published_at?->toISOString(),
            'slug' => $article->slug,
            'title' => $article->title,
            'videos' => $article->videos
                ->map(fn (ArticleVideo $video): array => [
                    'id' => $video->id,
                    'sortOrder' => $video->sort_order,
                    'youtubeId' => $video->youtube_id,
                ])
                ->all(),
        ];
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
            'coverImage' => $this->imageData(
                $article->cover_image_name,
                $article->cover_image_width,
                $article->cover_image_height,
                'cover',
            ),
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

    /**
     * @return array<string, mixed>|null
     */
    private function imageData(?string $name, ?int $width, ?int $height, string $role): ?array
    {
        if (! filled($name)) {
            return null;
        }

        if (! $this->uploadedImages->isUploadedName($name)) {
            return [
                'height' => $height,
                'name' => $name,
                'source' => 'static',
                'width' => $width,
            ];
        }

        if (! is_int($width) || ! is_int($height) || $width < 1 || $height < 1) {
            return null;
        }

        return [
            'height' => $height,
            'name' => $name,
            'source' => 'upload',
            'width' => $width,
            'widths' => $this->uploadedImages->widthsFor($role, $width),
        ];
    }
}
