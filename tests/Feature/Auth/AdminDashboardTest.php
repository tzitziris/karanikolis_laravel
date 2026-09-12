<?php

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('lists every article for the owner with distinct Greek visibility states', function () {
    $hidden = Article::factory()->create([
        'is_visible' => false,
        'published_at' => now()->subDays(5),
        'title' => 'Κρυφό άρθρο',
    ]);
    $undated = Article::factory()->visible()->create([
        'published_at' => null,
        'title' => 'Ορατό χωρίς ημερομηνία',
    ]);
    $scheduled = Article::factory()->visible()->create([
        'published_at' => now()->addDay(),
        'title' => 'Μελλοντικό άρθρο',
    ]);
    $live = Article::factory()->published()->create([
        'published_at' => now()->subDay(),
        'title' => 'Ζωντανό άρθρο',
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('articles', 4)
            ->where('articles.0.title', $scheduled->title)
            ->where('articles.0.state.label', 'Προγραμματισμένο')
            ->where('articles.0.state.canBeSeen', false)
            ->where('articles.1.title', $live->title)
            ->where('articles.1.state.label', 'Ζωντανό')
            ->where('articles.1.state.canBeSeen', true)
            ->where('articles.2.title', $hidden->title)
            ->where('articles.2.state.label', 'Κρυφό')
            ->where('articles.2.state.canBeSeen', false)
            ->where('articles.3.title', $undated->title)
            ->where('articles.3.state.label', 'Ορατό χωρίς ημερομηνία')
            ->where('articles.3.state.canBeSeen', false)
        );
});

it('loads the admin article list in one article query without article bodies', function () {
    Article::factory()
        ->count(3)
        ->has(ArticleImage::factory()->count(2), 'images')
        ->has(ArticleVideo::factory()->count(1), 'videos')
        ->create();
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, '`articles`')) {
            $queries[] = $query->sql;
        }
    });

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk();

    expect($queries)->toHaveCount(1)
        ->and($queries[0])->toContain('select `id`, `title`, `slug`, `excerpt`, `cover_image_name`, `is_visible`, `published_at`, `created_at`, `updated_at`')
        ->and($queries[0])->toContain('`images_count`')
        ->and($queries[0])->toContain('`videos_count`')
        ->and($queries[0])->not->toContain('`body`');
});

it('publishes and unpublishes without forgetting the stored publication date', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create([
        'is_visible' => false,
        'published_at' => null,
        'title' => 'Άρθρο προς δημοσίευση',
    ]);

    $this->actingAs($user)
        ->patch("/admin/articles/{$article->id}/publish")
        ->assertRedirect('/admin')
        ->assertSessionHas('success', 'Το άρθρο «Άρθρο προς δημοσίευση» δημοσιεύτηκε.');

    $article->refresh();
    $firstPublishedAt = $article->published_at;

    expect($article->is_visible)->toBeTrue()
        ->and($firstPublishedAt)->not->toBeNull();

    $this->actingAs($user)
        ->patch("/admin/articles/{$article->id}/unpublish")
        ->assertRedirect('/admin')
        ->assertSessionHas('success', 'Το άρθρο «Άρθρο προς δημοσίευση» αποσύρθηκε από τη δημόσια σελίδα.');

    $article->refresh();

    expect($article->is_visible)->toBeFalse()
        ->and($article->published_at->equalTo($firstPublishedAt))->toBeTrue();

    $this->actingAs($user)
        ->patch("/admin/articles/{$article->id}/publish")
        ->assertRedirect('/admin');

    $article->refresh();

    expect($article->is_visible)->toBeTrue()
        ->and($article->published_at->equalTo($firstPublishedAt))->toBeTrue();
});

it('deletes an article and its database media records from a non-link action route', function () {
    $article = Article::factory()
        ->has(ArticleImage::factory()->count(2), 'images')
        ->has(ArticleVideo::factory()->count(1), 'videos')
        ->create(['title' => 'Άρθρο για διαγραφή']);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/articles/{$article->id}")
        ->assertRedirect('/admin')
        ->assertSessionHas('success', 'Το άρθρο «Άρθρο για διαγραφή» διαγράφηκε οριστικά.');

    $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    $this->assertDatabaseMissing('article_images', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('article_videos', ['article_id' => $article->id]);
});

it('keeps data-changing dashboard actions off links and names deleted media in Greek', function () {
    $dashboard = File::get(resource_path('js/Pages/Admin/Dashboard.jsx'));

    expect($dashboard)
        ->toContain('router.patch(`/admin/articles/${article.id}/publish`')
        ->toContain('router.patch(`/admin/articles/${article.id}/unpublish`')
        ->toContain('router.delete(`/admin/articles/${article.id}`')
        ->toContain('window.confirm(deleteMessage(article))')
        ->toContain('Θα χαθούν μαζί του')
        ->toContain('Η διαγραφή δεν αναιρείται')
        ->not->toContain('<Link')
        ->not->toContain('href={`/admin/articles/${article.id}/publish`')
        ->not->toContain('href={`/admin/articles/${article.id}/unpublish`')
        ->not->toContain('href={`/admin/articles/${article.id}/delete`');
});

it('redirects unauthenticated mutation requests to the sign-in page before changing data', function (string $method, string $uri) {
    $article = Article::factory()->create([
        'is_visible' => false,
        'published_at' => null,
    ]);

    $this->{$method}(str_replace('{article}', (string) $article->id, $uri))
        ->assertRedirect('/admin/login');

    expect($article->refresh()->is_visible)->toBeFalse()
        ->and($article->published_at)->toBeNull();
})->with([
    ['patch', '/admin/articles/{article}/publish'],
    ['patch', '/admin/articles/{article}/unpublish'],
    ['delete', '/admin/articles/{article}'],
]);
