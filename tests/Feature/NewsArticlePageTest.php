<?php

use App\Models\Article;
use App\Services\ArticleBodyRenderer;
use App\Support\ArticleSlug;
use Database\Seeders\LocalNewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('serves a public article with server-rendered body html and eager-loaded media', function () {
    $this->seed(LocalNewsSeeder::class);
    $article = Article::where('title', 'Αγωνιστική ημέρα στην Καβάλα')->firstOrFail();

    $this->get("/news/{$article->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article')
            ->where('article.title', 'Αγωνιστική ημέρα στην Καβάλα')
            ->where('article.slug', $article->slug)
            ->where('article.bodyHtml', app(ArticleBodyRenderer::class)->render($article->body))
            ->where('article.date', '28 Αυγούστου 2026')
            ->has('article.gallery', 2)
            ->where('article.gallery.0.imageName', $article->images()->firstOrFail()->image_name)
            ->has('article.videos', 1)
            ->where('article.videos.0.youtubeId', 'M7lc1UVf-VE')
        );

    $props = $this->get("/news/{$article->slug}")->inertiaProps('article');

    expect($props)
        ->toHaveKey('bodyHtml')
        ->not->toHaveKey('body');
});

it('uses the shared public article rule for typed article addresses', function () {
    $this->seed(LocalNewsSeeder::class);
    $hidden = Article::where('title', 'Προσχέδιο ανακοίνωσης για αγώνες')->firstOrFail();
    $undated = Article::where('title', 'Ορατό χωρίς ημερομηνία δημοσίευσης')->firstOrFail();
    $future = Article::factory()->visible()->create([
        'published_at' => now()->addWeek(),
        'title' => 'Μελλοντική ανακοίνωση',
    ]);

    foreach ([$hidden, $undated, $future] as $article) {
        $this->get("/news/{$article->slug}")
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('NotFound')
            );
    }
});

it('shows the Greek not found page for a missing article slug', function () {
    $this->get('/news/den-yparchei')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('NotFound')
        );
});

it('does not make the article page one query per gallery image or video', function () {
    $this->seed(LocalNewsSeeder::class);
    $article = Article::where('title', 'Αγωνιστική ημέρα στην Καβάλα')->firstOrFail();
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (
            str_contains($query->sql, '`articles`')
            || str_contains($query->sql, '`article_images`')
            || str_contains($query->sql, '`article_videos`')
        ) {
            $queries[] = $query->sql;
        }
    });

    $this->get("/news/{$article->slug}")->assertOk();

    expect($queries)->toHaveCount(3)
        ->and($queries[0])->toContain('from `articles`')
        ->and($queries[1])->toContain('from `article_images`')
        ->and($queries[2])->toContain('from `article_videos`');
});

it('keeps the video placeholder off third-party networks until play is pressed', function () {
    $source = File::get(resource_path('js/Components/News/YoutubeEmbed.jsx'));

    expect($source)
        ->not->toContain('i.ytimg.com')
        ->not->toContain('img.youtube')
        ->not->toContain('maxresdefault')
        ->not->toContain('hqdefault')
        ->toContain('isLoaded ? (')
        ->toContain('https://www.youtube-nocookie.com/embed/${youtubeId}?autoplay=1');
});

it('makes gallery enlargement a keyboard-safe dialog', function () {
    $source = File::get(resource_path('js/Components/News/ArticleGallery.jsx'));

    expect($source)
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain("appRoot?.setAttribute('inert', '')")
        ->toContain("appRoot?.removeAttribute('inert')")
        ->toContain("event.key === 'Escape'")
        ->toContain("event.key === 'ArrowLeft'")
        ->toContain("event.key === 'ArrowRight'")
        ->toContain("event.key !== 'Tab'")
        ->toContain('document.body.style.overflow')
        ->toContain('restoreFocusRef.current?.focus({ preventScroll: true })')
        ->toContain('createPortal(dialog, document.body)');
});

it('keeps long seeded article titles wrapping by whole words', function () {
    $this->seed(LocalNewsSeeder::class);
    $longest = Article::readyForPublic()
        ->get()
        ->sortByDesc(fn (Article $article): int => mb_strlen($article->title))
        ->first();
    $source = File::get(resource_path('js/Pages/Article.jsx'));

    expect($longest)->toBeInstanceOf(Article::class);

    $this->get("/news/{$longest->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article')
            ->where('article.title', $longest->title)
        );

    expect($source)
        ->toContain('[overflow-wrap:normal]')
        ->toContain('[word-break:normal]')
        ->not->toContain('break-words')
        ->not->toContain('break-all')
        ->and(ArticleSlug::fromTitle($longest->title))->toBe($longest->slug);
});
