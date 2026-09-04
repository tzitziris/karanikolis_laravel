<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleImage>
 */
class ArticleImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alt_text' => fake()->randomElement([
                'Αθλητές στην προπόνηση',
                'Στιγμιότυπο από την ομάδα',
                'Τεχνική στα λάκτισματα',
                'Παιδικό τμήμα στο τατάμι',
            ]),
            'article_id' => Article::factory(),
            'height' => 1333,
            'image_name' => fake()->randomElement(array_keys(config('images.static.photos', ['hero-kickboxing' => '']))),
            'sort_order' => fake()->numberBetween(0, 4),
            'width' => 2000,
        ];
    }
}
