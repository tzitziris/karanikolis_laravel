<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Προπόνηση αγωνιστικού τμήματος στην Ελευθερούπολη',
            'Νέες επιτυχίες για τους μικρούς μαχητές',
            'Σεμινάριο τεχνικής και αυτοπεποίθησης',
            'Προετοιμασία για το επόμενο πρωτάθλημα',
        ]);

        return [
            'body' => self::tiptapDocument([
                fake()->sentence(12),
                fake()->sentence(14),
            ]),
            'cover_image_height' => 1333,
            'cover_image_name' => fake()->randomElement(array_keys(config('images.static.photos', ['hero-kickboxing' => '']))),
            'cover_image_width' => 2000,
            'excerpt' => fake()->sentence(18),
            'is_visible' => false,
            'published_at' => null,
            'title' => $title,
        ];
    }

    public function visible(): static
    {
        return $this->state(fn (): array => [
            'is_visible' => true,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'is_visible' => true,
            'published_at' => fake()->dateTimeBetween('-90 days', '-1 day'),
        ]);
    }

    /**
     * @param  array<int, string>  $paragraphs
     * @return array<string, mixed>
     */
    public static function tiptapDocument(array $paragraphs): array
    {
        return [
            'content' => array_map(
                fn (string $paragraph): array => [
                    'content' => [
                        [
                            'text' => $paragraph,
                            'type' => 'text',
                        ],
                    ],
                    'type' => 'paragraph',
                ],
                $paragraphs,
            ),
            'type' => 'doc',
        ];
    }
}
