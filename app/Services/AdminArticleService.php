<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function replaceCover(Article $article, UploadedFile $file, ?string $altText): void
    {
        $stored = $this->uploadedImages->store($file, 'cover');
        $oldName = null;

        try {
            $oldName = DB::transaction(function () use ($altText, $article, $stored): ?string {
                $oldName = $article->cover_image_name;

                $article->forceFill([
                    'cover_image_height' => $stored['height'],
                    'cover_image_alt_text' => $this->cleanAltText($altText),
                    'cover_image_name' => $stored['name'],
                    'cover_image_width' => $stored['width'],
                ])->save();

                return $oldName;
            });
        } catch (\Throwable $exception) {
            $this->uploadedImages->deleteUnreferenced([$stored['name']]);

            throw $exception;
        }

        $this->uploadedImages->deleteUnreferenced([$oldName]);
    }

    public function removeCover(Article $article): void
    {
        $name = DB::transaction(function () use ($article): ?string {
            $name = $article->cover_image_name;

            $article->forceFill([
                'cover_image_height' => null,
                'cover_image_alt_text' => null,
                'cover_image_name' => null,
                'cover_image_width' => null,
            ])->save();

            return $name;
        });

        $this->uploadedImages->deleteUnreferenced([$name]);
    }

    public function addGalleryImage(Article $article, UploadedFile $file, ?string $altText): void
    {
        $stored = $this->uploadedImages->store($file, 'gallery');

        try {
            DB::transaction(function () use ($article, $altText, $stored): void {
                $article->images()->create([
                    'alt_text' => $this->cleanAltText($altText),
                    'height' => $stored['height'],
                    'image_name' => $stored['name'],
                    'sort_order' => ((int) ($article->images()->max('sort_order') ?? -1)) + 1,
                    'width' => $stored['width'],
                ]);
            });
        } catch (\Throwable $exception) {
            $this->uploadedImages->deleteUnreferenced([$stored['name']]);

            throw $exception;
        }
    }

    public function deleteGalleryImage(ArticleImage $image): void
    {
        $name = DB::transaction(function () use ($image): ?string {
            $articleId = $image->article_id;
            $name = $image->image_name;
            $image->delete();
            $this->compactGalleryOrder($articleId);

            return $name;
        });

        $this->uploadedImages->deleteUnreferenced([$name]);
    }

    /**
     * @param  array<int, array{id?: int, alt_text?: string|null}>  $images
     */
    public function saveGalleryOrder(Article $article, array $images): void
    {
        DB::transaction(function () use ($article, $images): void {
            $ownedIds = $article->images()->pluck('id')->all();
            $submittedIds = collect($images)
                ->pluck('id')
                ->filter(fn (mixed $id): bool => is_numeric($id))
                ->map(fn (mixed $id): int => (int) $id)
                ->values()
                ->all();

            if (
                count($submittedIds) !== count(array_unique($submittedIds))
                || array_diff($ownedIds, $submittedIds) !== []
                || array_diff($submittedIds, $ownedIds) !== []
            ) {
                throw ValidationException::withMessages([
                    'gallery' => 'Η σειρά των φωτογραφιών δεν είναι έγκυρη.',
                ]);
            }

            foreach (array_values($images) as $index => $image) {
                $article->images()
                    ->whereKey($image['id'])
                    ->update([
                        'alt_text' => $this->cleanAltText($image['alt_text'] ?? null),
                        'sort_order' => $index,
                    ]);
            }
        });
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

    private function cleanAltText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function compactGalleryOrder(int $articleId): void
    {
        ArticleImage::query()
            ->where('article_id', $articleId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (ArticleImage $image, int $index): void {
                $image->forceFill(['sort_order' => $index])->save();
            });
    }
}
