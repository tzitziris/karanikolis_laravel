<?php

namespace App\Models;

use App\Support\YouTubeVideo;
use Database\Factories\ArticleVideoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'article_id',
    'youtube_url',
    'youtube_id',
    'sort_order',
])]
class ArticleVideo extends Model
{
    /** @use HasFactory<ArticleVideoFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected static function booted(): void
    {
        static::saving(function (ArticleVideo $video): void {
            if (! filled($video->youtube_id) && filled($video->youtube_url)) {
                $video->youtube_id = YouTubeVideo::idFromUrl($video->youtube_url) ?? '';
            }
        });
    }

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
