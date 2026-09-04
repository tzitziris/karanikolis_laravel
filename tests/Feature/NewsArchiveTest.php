<?php

use App\Models\Article;
use App\Services\ArticleFeed;
use Database\Seeders\LocalNewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('serves the news archive with only public-ready article card data', function () {
    $this->seed(LocalNewsSeeder::class);
    Article::factory()->visible()->create([
        'published_at' => now()->addWeek(),
        'title' => 'Μελλοντικό νέο',
    ]);
    Article::factory()->published()->create([
        'cover_image_name' => null,
        'published_at' => now(),
        'title' => 'Δημοσιευμένο χωρίς εικόνα',
    ]);

    $this->get('/news')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('News')
            ->has('articles', ArticleFeed::NEWS_PAGE_SIZE)
            ->where('articles.0.title', 'Δημοσιευμένο χωρίς εικόνα')
            ->where('articles.0.coverImageName', null)
            ->where('articles.0.date', now()->locale('el')->translatedFormat('j F Y'))
            ->where('pagination.currentPage', 1)
            ->where('pagination.total', 17)
            ->where('pagination.lastPage', 2)
        );

    $responseTitles = collect($this->get('/news')->inertiaProps('articles'))->pluck('title');

    expect($responseTitles)
        ->not->toContain('Ορατό χωρίς ημερομηνία δημοσίευσης')
        ->not->toContain('Προσχέδιο ανακοίνωσης για αγώνες')
        ->not->toContain('Μελλοντικό νέο');
});

it('serves addressable archive pages from the page query string', function () {
    $this->seed(LocalNewsSeeder::class);

    $this->get('/news?page=2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('News')
            ->has('articles', 7)
            ->where('articles.0.title', 'Συνέπεια στο τμήμα ενηλίκων')
            ->where('pagination.currentPage', 2)
            ->where('pagination.from', 10)
            ->where('pagination.to', 16)
            ->where('pagination.total', 16)
        );
});

it('redirects archive pages that do not exist to the nearest real archive page', function () {
    $this->seed(LocalNewsSeeder::class);

    $this->get('/news?page=99')
        ->assertRedirect('/news?page=2');
});

it('redirects empty archive pages back to the empty first page', function () {
    $this->get('/news?page=2')
        ->assertRedirect('/news');

    $this->get('/news')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('News')
            ->has('articles', 0)
            ->where('pagination.total', 0)
        );
});

it('feeds latest home articles through the shared article feed', function () {
    $this->seed(LocalNewsSeeder::class);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('articles', ArticleFeed::HOME_LIMIT)
            ->where('articles.0.title', 'Αγωνιστική ημέρα στην Καβάλα')
            ->where('articles.1.title', 'Οι μικροί μαχητές δυναμώνουν')
            ->where('articles.2.title', 'Προπόνηση με ενέργεια και χαμόγελα')
        );
});

it('keeps the public visibility rule in the shared article feed', function () {
    $feed = File::get(app_path('Services/ArticleFeed.php'));
    $controllers = collect(File::files(app_path('Http/Controllers')))
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->implode("\n");

    expect($feed)->toContain('->readyForPublic()')
        ->and($feed)->toContain('->publicationOrder()')
        ->and($controllers)->not->toContain("where('is_visible'")
        ->and($controllers)->not->toContain('whereNotNull(\'published_at\'')
        ->and($controllers)->not->toContain('orderByDesc(\'published_at\'');
});

it('loads archive cards with one count query and one card query without article bodies', function () {
    $this->seed(LocalNewsSeeder::class);
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, '`articles`')) {
            $queries[] = $query->sql;
        }
    });

    $this->get('/news')->assertOk();

    expect($queries)->toHaveCount(2)
        ->and($queries[0])->toContain('count(*)')
        ->and($queries[1])->toContain('select `id`, `title`, `slug`, `excerpt`, `cover_image_name`, `cover_image_width`, `cover_image_height`, `published_at`')
        ->and($queries[1])->not->toContain('`body`');
});
