<?php

namespace App\Models;

use App\Support\ArticleSlug;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'slug',
    'excerpt',
    'body',
    'cover_image_name',
    'cover_image_width',
    'cover_image_height',
    'is_visible',
    'published_at',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Article $article): void {
            $article->slug = ArticleSlug::uniqueForTitle(
                filled($article->slug) ? $article->slug : $article->title,
            );
        });
    }

    /**
     * @return HasMany<ArticleImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<ArticleVideo, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(ArticleVideo::class)->orderBy('sort_order');
    }

    /**
     * @param  Builder<Article>  $query
     */
    public function scopeReadyForPublic(Builder $query): void
    {
        $query
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Article>  $query
     */
    public function scopePublicationOrder(Builder $query): void
    {
        $query->orderByDesc('published_at');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'body' => 'array',
            'cover_image_height' => 'integer',
            'cover_image_width' => 'integer',
            'is_visible' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
