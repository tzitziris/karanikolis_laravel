<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleImage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminArticleService
{
    public function __construct(private readonly UploadedArticleImageService $uploadedImages) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForDashboard(): array
    {
        return Article::query()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'cover_image_name',
                'is_visible',
                'published_at',
                'created_at',
                'updated_at',
            ])
            ->withCount(['images', 'videos'])
            ->orderByRaw('published_at is null')
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Article $article): array => $this->dashboardData($article))
            ->all();
    }

    public function publish(Article $article): void
    {
        $article->forceFill([
            'is_visible' => true,
            'published_at' => $article->published_at ?? now(),
        ])->save();
    }

    public function unpublish(Article $article): void
    {
        $article->forceFill([
            'is_visible' => false,
        ])->save();
    }

    public function delete(Article $article): void
    {
        $names = DB::transaction(function () use ($article): array {
            $article->loadMissing('images:id,article_id,image_name');
            $names = [
                $article->cover_image_name,
                ...$article->images->pluck('image_name')->all(),
            ];

            $article->delete();

            return $names;
        });

        $this->uploadedImages->deleteUnreferenced($names);
    }

    public function deleteGalleryImage(ArticleImage $image): void
    {
        $name = DB::transaction(function () use ($image): ?string {
            $name = $image->image_name;
            $image->delete();

            return $name;
        });

        $this->uploadedImages->deleteUnreferenced([$name]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardData(Article $article): array
    {
        $state = $this->stateFor($article);

        return [
            'coverImageName' => $article->cover_image_name,
            'createdAt' => $article->created_at?->toISOString(),
            'createdDate' => $this->dateForGreekReader($article->created_at),
            'excerpt' => $article->excerpt,
            'id' => $article->id,
            'imageCount' => $article->images_count,
            'isVisible' => $article->is_visible,
            'publishedAt' => $article->published_at?->toISOString(),
            'publishedDate' => $this->dateForGreekReader($article->published_at),
            'slug' => $article->slug,
            'state' => $state,
            'title' => $article->title,
            'updatedAt' => $article->updated_at?->toISOString(),
            'updatedDate' => $this->dateForGreekReader($article->updated_at),
            'videoCount' => $article->videos_count,
        ];
    }

    /**
     * @return array{key: string, label: string, detail: string, canBeSeen: bool}
     */
    private function stateFor(Article $article): array
    {
        if (! $article->is_visible) {
            return [
                'canBeSeen' => false,
                'detail' => 'Δεν εμφανίζεται δημόσια, αλλά κρατά την ημερομηνία του.',
                'key' => 'hidden',
                'label' => 'Κρυφό',
            ];
        }

        if ($article->published_at === null) {
            return [
                'canBeSeen' => false,
                'detail' => 'Είναι σημειωμένο ως ορατό, αλλά δεν έχει ημερομηνία δημοσίευσης.',
                'key' => 'undated',
                'label' => 'Ορατό χωρίς ημερομηνία',
            ];
        }

        if ($article->published_at->isFuture()) {
            return [
                'canBeSeen' => false,
                'detail' => 'Θα εμφανιστεί όταν φτάσει η ημερομηνία δημοσίευσης.',
                'key' => 'scheduled',
                'label' => 'Προγραμματισμένο',
            ];
        }

        return [
            'canBeSeen' => true,
            'detail' => 'Εμφανίζεται στη δημόσια σελίδα ειδήσεων.',
            'key' => 'live',
            'label' => 'Ζωντανό',
        ];
    }

    private function dateForGreekReader(?Carbon $date): ?string
    {
        return $date?->locale('el')->translatedFormat('j F Y, H:i');
    }
}
