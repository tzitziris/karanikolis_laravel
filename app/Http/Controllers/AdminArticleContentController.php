<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleContentRequest;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleVideo;
use App\Services\AdminArticleService;
use App\Services\UploadedArticleImageService;
use App\Support\ArticleBodyContract;
use App\Support\UploadLimits;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminArticleContentController extends Controller
{
    public function __construct(private readonly UploadedArticleImageService $uploadedImages) {}

    public function create(): Response
    {
        return Inertia::render('Admin/ArticleForm', [
            'article' => null,
            'bodyContract' => $this->bodyContract(),
            'mode' => 'create',
            'uploadLimits' => $this->uploadLimits(),
        ]);
    }

    public function store(ArticleContentRequest $request): RedirectResponse
    {
        $article = Article::create([
            'body' => $request->validated('body'),
            'excerpt' => trim((string) $request->validated('excerpt')),
            'is_visible' => false,
            'published_at' => $request->date('published_at'),
            'title' => trim((string) $request->validated('title')),
        ]);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Το άρθρο δημιουργήθηκε ως προσχέδιο.');
    }

    public function edit(Article $article): Response
    {
        $article->loadMissing([
            'images:id,article_id,image_name,alt_text,width,height,sort_order',
            'videos:id,article_id,youtube_url,youtube_id,sort_order',
        ]);

        return Inertia::render('Admin/ArticleForm', [
            'article' => $this->articleData($article),
            'bodyContract' => $this->bodyContract(),
            'mode' => 'edit',
            'uploadLimits' => $this->uploadLimits(),
        ]);
    }

    public function update(ArticleContentRequest $request, Article $article, AdminArticleService $articles): RedirectResponse
    {
        $keptDate = ! $request->filled('published_at') && $article->published_at !== null;

        $article->update([
            'body' => $request->validated('body'),
            'excerpt' => trim((string) $request->validated('excerpt')),
            'published_at' => $request->filled('published_at') ? $request->date('published_at') : $article->published_at,
            'title' => trim((string) $request->validated('title')),
        ]);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', $this->savedMessage($article, $articles, $keptDate));
    }

    /**
     * Saving must never be able to report success while quietly doing something
     * else: taking the article off the public site, or ignoring an emptied date.
     */
    private function savedMessage(Article $article, AdminArticleService $articles, bool $keptDate): string
    {
        $message = 'Το άρθρο αποθηκεύτηκε. '.$articles->stateFor($article->refresh())['detail'];

        if ($keptDate) {
            $message .= ' Το πεδίο της ημερομηνίας ήταν άδειο, οπότε κρατήθηκε η ημερομηνία που είχε ήδη.';
        }

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    private function articleData(Article $article): array
    {
        return [
            'body' => $article->body,
            'excerpt' => $article->excerpt,
            'coverImage' => $this->imageData(
                $article->cover_image_name,
                $article->cover_image_width,
                $article->cover_image_height,
                'cover',
            ),
            'coverImageAltText' => $article->cover_image_alt_text,
            'coverImageHeight' => $article->cover_image_height,
            'coverImageName' => $article->cover_image_name,
            'coverImageWidth' => $article->cover_image_width,
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
            'isVisible' => $article->is_visible,
            'publishedAt' => $article->published_at?->format('Y-m-d\TH:i'),
            'slug' => $article->slug,
            'title' => $article->title,
            'videos' => $article->videos
                ->map(fn (ArticleVideo $video): array => [
                    'id' => $video->id,
                    'sortOrder' => $video->sort_order,
                    'youtubeId' => $video->youtube_id,
                    'youtubeUrl' => $video->youtube_url,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bodyContract(): array
    {
        return [
            'alignments' => ArticleBodyContract::alignments(),
            'headingLevels' => ArticleBodyContract::headingLevels(),
            'marks' => ArticleBodyContract::editableMarks(),
            'nodes' => ArticleBodyContract::editableNodes(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function imageData(?string $name, ?int $width, ?int $height, string $role): ?array
    {
        if (! $this->uploadedImages->isUploadedName($name) || ! is_int($width) || ! is_int($height)) {
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

    /**
     * @return array<string, int|string>
     */
    private function uploadLimits(): array
    {
        return [
            'maxBytes' => UploadLimits::articleImageMaxBytes(),
            'maxLabel' => UploadLimits::articleImageMaxLabel(),
            'maxPixels' => (int) config('images.uploads.limits.max_pixels'),
        ];
    }
}
