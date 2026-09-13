<?php

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

/**
 * The site prefetches on hover in seventeen places, so a visitor's browser
 * fires GET requests nobody asked for. Nothing reachable by GET may change
 * anything. These tests read the route table rather than a component's source,
 * so a route added later is covered without anyone remembering to add it.
 */
function domainSnapshot(): string
{
    return collect(['articles', 'article_images', 'article_videos', 'users'])
        ->map(fn (string $table): string => $table.':'.DB::table($table)->orderBy('id')->get()->toJson())
        ->implode("\n");
}

/**
 * @return array<string, string>
 */
function routeParameterValues(): array
{
    // {article} is deliberately the hidden one. A stray GET that published an
    // article would leave a published fixture exactly as it found it, and the
    // test would pass while the bug shipped.
    $hidden = Article::query()->where('is_visible', false)->firstOrFail();
    $published = Article::query()->readyForPublic()->firstOrFail();

    return [
        'adminPath' => 'kati',
        'article' => (string) $hidden->id,
        'image' => (string) ArticleImage::query()->value('id'),
        'slug' => $published->slug,
        'video' => (string) ArticleVideo::query()->value('id'),
    ];
}

function fillRouteParameters(RoutingRoute $route): ?string
{
    $uri = '/'.ltrim($route->uri(), '/');

    foreach (routeParameterValues() as $name => $value) {
        $uri = str_replace(['{'.$name.'}', '{'.$name.'?}'], $value, $uri);
    }

    return str_contains($uri, '{') ? null : $uri;
}

beforeEach(function () {
    Article::factory()->published()->create();

    $hidden = Article::factory()->create(['is_visible' => false, 'published_at' => null]);
    ArticleImage::factory()->create(['article_id' => $hidden->id]);
    ArticleVideo::factory()->create(['article_id' => $hidden->id]);
});

it('changes nothing when any page of the site is fetched', function () {
    $this->actingAs(User::factory()->create());

    $before = domainSnapshot();
    $visited = [];

    foreach (Route::getRoutes() as $route) {
        if (! in_array('GET', $route->methods(), true)) {
            continue;
        }

        $uri = fillRouteParameters($route);

        if ($uri === null || $uri === '/up') {
            continue;
        }

        $this->get($uri);
        $visited[] = $uri;
    }

    expect(count($visited))->toBeGreaterThan(8);
    expect(domainSnapshot())->toBe($before, 'A GET request changed the database. Visited: '.implode(', ', $visited));
});

it('refuses to run a mutation that arrives as a GET', function () {
    $this->actingAs(User::factory()->create());

    // The sign-in address is served by a GET route of its own as well as by the
    // POST that signs you in, so it is not one of these; the property is about
    // addresses that exist only to change something.
    $servedByGet = collect(Route::getRoutes())
        ->filter(fn (RoutingRoute $route): bool => in_array('GET', $route->methods(), true))
        ->map(fn (RoutingRoute $route): ?string => fillRouteParameters($route))
        ->filter()
        ->all();

    $mutations = collect(Route::getRoutes())
        ->filter(fn (RoutingRoute $route): bool => array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $route->methods()) !== [])
        ->map(fn (RoutingRoute $route): ?string => fillRouteParameters($route))
        ->filter()
        ->reject(fn (string $uri): bool => in_array($uri, $servedByGet, true))
        ->unique()
        ->values();

    // Publishing, unpublishing and deleting an article are the ones that would
    // hurt: a crawler or a prefetch would empty the news page by itself.
    $id = Article::query()->where('is_visible', false)->value('id');
    expect($mutations)->toContain("/admin/articles/{$id}/publish");
    expect($mutations)->toContain("/admin/articles/{$id}/unpublish");
    expect($mutations)->toContain("/admin/articles/{$id}");
    expect($mutations->count())->toBeGreaterThan(6);

    $before = domainSnapshot();
    $answered = [];

    foreach ($mutations as $uri) {
        $answered[$uri] = $this->get($uri)->getStatusCode();
    }

    expect(collect($answered)->reject(fn (int $status): bool => in_array($status, [404, 405], true))->all())
        ->toBe([], 'These mutation addresses answered a GET with something other than a refusal.');

    expect(domainSnapshot())->toBe($before, 'A GET to a mutation address changed the database.');
});
