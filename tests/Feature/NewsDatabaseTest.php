<?php

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleVideo;
use App\Support\ArticleSlug;
use App\Support\YouTubeVideo;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LocalNewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the news tables with integer keys and image identifiers', function () {
    expect(Schema::hasColumns('articles', [
        'id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image_name',
        'cover_image_width',
        'cover_image_height',
        'is_visible',
        'published_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('article_images', [
            'id',
            'article_id',
            'image_name',
            'alt_text',
            'width',
            'height',
            'sort_order',
            'created_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('article_videos', [
            'id',
            'article_id',
            'youtube_url',
            'youtube_id',
            'sort_order',
            'created_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('articles', 'cover_image'))->toBeFalse()
        ->and(Schema::hasColumn('article_images', 'url'))->toBeFalse();
});

it('generates url safe Greek slugs once and resolves collisions before insert', function () {
    $first = Article::factory()->create([
        'title' => 'Αγωνιστική Δράση στην Καβάλα!',
    ]);
    $second = Article::factory()->create([
        'title' => 'αγωνιστικη δραση στην καβαλα',
    ]);
    $third = Article::factory()->create([
        'slug' => 'agonistiki-drasi-stin-kavala',
        'title' => 'Διαφορετικός τίτλος',
    ]);

    expect(ArticleSlug::fromTitle('Αγωνιστική Δράση στην Καβάλα!'))->toBe('agonistiki-drasi-stin-kavala')
        ->and(ArticleSlug::fromTitle('Μαχητές Ελευθερούπολης'))->toBe('machites-eleftheroupolis')
        ->and(ArticleSlug::fromTitle('Προπόνηση με μπάλες και τσάντες'))->toBe('proponisi-me-bales-kai-tsantes')
        ->and(ArticleSlug::fromTitle('Ελευθερούπολη'))->toBe('eleftheroupoli')
        ->and(ArticleSlug::fromTitle('ουρανός'))->toBe('ouranos')
        ->and(ArticleSlug::fromTitle('Ευρώπη'))->toBe('evropi')
        ->and(ArticleSlug::fromTitle('αύριο'))->toBe('avrio')
        ->and(ArticleSlug::fromTitle('σύγκρουση'))->toBe('sygkrousi')
        ->and(ArticleSlug::fromTitle('τσάντα'))->toBe('tsanta')
        ->and(ArticleSlug::fromTitle('άγκυρα'))->toBe('agkyra')
        ->and($first->slug)->toBe('agonistiki-drasi-stin-kavala')
        ->and($second->slug)->toBe('agonistiki-drasi-stin-kavala-2')
        ->and($third->slug)->toBe('agonistiki-drasi-stin-kavala-3');

    $first->update(['title' => 'Νέος τίτλος μετά τη δημοσίευση']);

    expect($first->refresh()->slug)->toBe('agonistiki-drasi-stin-kavala');
});

it('orders public articles only by publication timestamp', function () {
    $oldest = Article::factory()->published()->create([
        'published_at' => now()->subDays(5),
        'title' => 'Παλαιότερη δημοσίευση',
    ]);
    $newest = Article::factory()->published()->create([
        'published_at' => now()->subDay(),
        'title' => 'Νεότερη δημοσίευση',
    ]);
    Article::factory()->visible()->create([
        'published_at' => null,
        'title' => 'Ορατό χωρίς δημοσίευση',
    ]);
    Article::factory()->create([
        'is_visible' => false,
        'published_at' => now(),
        'title' => 'Κρυφή δημοσίευση',
    ]);
    Article::factory()->visible()->create([
        'published_at' => now()->addDay(),
        'title' => 'Μελλοντική δημοσίευση',
    ]);

    $articles = Article::query()
        ->readyForPublic()
        ->publicationOrder()
        ->pluck('id')
        ->all();

    expect($articles)->toBe([$newest->id, $oldest->id]);
});

it('deletes article media with the article and returns media in sort order', function () {
    $article = Article::factory()
        ->has(ArticleImage::factory()->state(['sort_order' => 20, 'image_name' => 'sparring']), 'images')
        ->has(ArticleImage::factory()->state(['sort_order' => 10, 'image_name' => 'pad-work']), 'images')
        ->has(ArticleVideo::factory()->state(['sort_order' => 5, 'youtube_url' => 'https://youtu.be/M7lc1UVf-VE', 'youtube_id' => null]), 'videos')
        ->create();

    expect($article->images()->pluck('image_name')->all())->toBe(['pad-work', 'sparring'])
        ->and($article->videos()->first()?->youtube_id)->toBe('M7lc1UVf-VE');

    $article->delete();

    expect(ArticleImage::query()->count())->toBe(0)
        ->and(ArticleVideo::query()->count())->toBe(0);
});

it('extracts YouTube ids from supported public URL shapes', function () {
    expect(YouTubeVideo::idFromUrl('https://www.youtube.com/watch?v=M7lc1UVf-VE'))->toBe('M7lc1UVf-VE')
        ->and(YouTubeVideo::idFromUrl('https://youtu.be/M7lc1UVf-VE'))->toBe('M7lc1UVf-VE')
        ->and(YouTubeVideo::idFromUrl('https://youtube.com/shorts/M7lc1UVf-VE'))->toBe('M7lc1UVf-VE')
        ->and(YouTubeVideo::idFromUrl('https://example.com/watch?v=M7lc1UVf-VE'))->toBeNull();
});

it('seeds believable local Greek news for list and article development', function () {
    $this->seed(LocalNewsSeeder::class);

    $articles = Article::query()->with(['images', 'videos'])->get();
    $facebookLike = $articles->firstWhere('title', 'Προπόνηση με ενέργεια και χαμόγελα');

    $bodyJson = $articles
        ->pluck('body')
        ->map(fn (array $body): string => json_encode($body, JSON_UNESCAPED_UNICODE))
        ->implode("\n");

    expect($articles)->toHaveCount(18)
        ->and($articles->where('is_visible', true)->whereNotNull('published_at'))->toHaveCount(16)
        ->and($articles->where('is_visible', false))->toHaveCount(1)
        ->and($articles->where('is_visible', true)->whereNull('published_at'))->toHaveCount(1)
        ->and($articles->flatMap->images)->not->toBeEmpty()
        ->and($articles->flatMap->videos)->not->toBeEmpty()
        ->and($facebookLike)->not->toBeNull()
        ->and($bodyJson)->toContain('#ΜαχητέςΕλευθερούπολης')
        ->and($bodyJson)->toContain('🥊')
        ->and($bodyJson)->toContain('"type":"heading"')
        ->and($bodyJson)->toContain('"type":"bulletList"')
        ->and($bodyJson)->toContain('"type":"orderedList"')
        ->and($bodyJson)->toContain('"type":"blockquote"')
        ->and($bodyJson)->toContain('"type":"image"')
        ->and($bodyJson)->toContain('"imageName":"ring-training"')
        ->and($bodyJson)->toContain('fbcdn.net')
        ->and($bodyJson)->toContain('"type":"bold"')
        ->and($bodyJson)->toContain('"type":"italic"')
        ->and($bodyJson)->toContain('"type":"link"');
});

it('seeds local news through the database seeder entrypoint', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Article::query()->count())->toBe(18)
        ->and(Article::query()->where('slug', '')->exists())->toBeFalse()
        ->and(ArticleVideo::query()->where('youtube_id', '')->exists())->toBeFalse();
});

it('guards the local news seeder from production database seeds', function () {
    $seeder = File::get(database_path('seeders/DatabaseSeeder.php'));

    expect($seeder)->toContain("app()->environment('production')")
        ->and($seeder)->toContain('return;')
        ->and($seeder)->toContain('LocalNewsSeeder::class');
});
