<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleVideo>
 */
class ArticleVideoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $youtubeId = fake()->randomElement([
            'dQw4w9WgXcQ',
            'M7lc1UVf-VE',
            'ysz5S6PUM-U',
        ]);

        return [
            'article_id' => Article::factory(),
            'sort_order' => fake()->numberBetween(0, 3),
            'youtube_id' => $youtubeId,
            'youtube_url' => "https://www.youtube.com/watch?v={$youtubeId}",
        ];
    }
}
