<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<int, string>
 */
function sitemapLocations(): array
{
    $response = test()->get('/sitemap.xml');
    $response->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('xml');

    $xml = simplexml_load_string($response->getContent());

    // A sitemap that does not parse is a sitemap no search engine reads, and
    // nothing on the site would ever show that.
    expect($xml)->not->toBeFalse('The sitemap is not valid XML.');

    $locations = [];

    foreach ($xml->url as $url) {
        $locations[] = (string) $url->loc;
    }

    return $locations;
}

it('lists every page of the site', function () {
    foreach ([route('home'), route('coaches'), route('schedule'), route('news'), route('about')] as $url) {
        expect(sitemapLocations())->toContain($url);
    }
});

it('lists exactly the articles the public site shows, and no others', function () {
    $live = Article::factory()->count(3)->published()->create();
    Article::factory()->create(['is_visible' => false, 'published_at' => now()->subDay()]);
    Article::factory()->visible()->create(['published_at' => null]);
    Article::factory()->visible()->create(['published_at' => now()->addYear()]);

    $listed = collect(sitemapLocations())->filter(fn (string $url): bool => str_contains($url, '/news/'))->values();

    // The comparison is against the database, not against what the sitemap says
    // about itself: whatever readyForPublic returns is the whole answer.
    $expected = Article::query()->readyForPublic()->pluck('slug')->map(fn (string $slug): string => route('news.show', $slug));

    expect($listed->sort()->values()->all())->toBe($expected->sort()->values()->all());
    expect($listed)->toHaveCount(3);
    expect($live->pluck('slug')->every(fn (string $slug): bool => $listed->contains(route('news.show', $slug))))->toBeTrue();
});

it('drops an article from the sitemap the moment it is taken off the site', function () {
    $article = Article::factory()->published()->create();

    expect(sitemapLocations())->toContain(route('news.show', $article->slug));

    $article->forceFill(['is_visible' => false])->save();

    expect(sitemapLocations())->not->toContain(route('news.show', $article->slug));
});

it('dates each article from its own row', function () {
    $article = Article::factory()->published()->create();

    $xml = simplexml_load_string(test()->get('/sitemap.xml')->getContent());
    $lastmod = null;

    foreach ($xml->url as $url) {
        if ((string) $url->loc === route('news.show', $article->slug)) {
            $lastmod = (string) $url->lastmod;
        }
    }

    expect($lastmod)->toBe($article->fresh()->updated_at->toAtomString());
});

it('tells crawlers where the sitemap is, at this site address', function () {
    test()->get('/robots.txt')
        ->assertOk()
        ->assertSee('Sitemap: '.route('sitemap'), false);
});
