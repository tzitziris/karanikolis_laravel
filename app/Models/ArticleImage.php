<?php

namespace App\Models;

use Database\Factories\ArticleImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'article_id',
    'image_name',
    'alt_text',
    'width',
    'height',
    'sort_order',
])]
class ArticleImage extends Model
{
    /** @use HasFactory<ArticleImageFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

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
            'height' => 'integer',
            'sort_order' => 'integer',
            'width' => 'integer',
        ];
    }
}
