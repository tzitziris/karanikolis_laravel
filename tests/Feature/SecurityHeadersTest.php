<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

/**
 * @return array<string, string>
 */
function contentSecurityPolicyOf(string $path): array
{
    $header = test()->get($path)->headers->get('Content-Security-Policy');

    expect($header)->not->toBeNull("No Content-Security-Policy on {$path}.");

    return collect(explode(';', $header))
        ->map(fn (string $directive): string => trim($directive))
        ->filter()
        ->mapWithKeys(function (string $directive): array {
            [$name, $value] = array_pad(explode(' ', $directive, 2), 2, '');

            return [$name => $value];
        })
        ->all();
}

it('sends the headers on every page a visitor can reach', function () {
    Article::factory()->published()->create();
    $slug = Article::query()->value('slug');

    foreach (['/', '/coaches', '/schedule', '/news', "/news/{$slug}", '/about', '/admin/login'] as $path) {
        $response = test()->get($path);

        expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff', $path);
        expect($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin', $path);
        expect($response->headers->get('X-Frame-Options'))->toBe('DENY', $path);
        expect($response->headers->get('Content-Security-Policy'))->not->toBeNull($path);
    }
});

it('sends the headers on the admin pages too', function () {
    $article = Article::factory()->create();

    foreach (['/admin', '/admin/articles/create', "/admin/articles/{$article->id}/edit"] as $path) {
        $response = test()->actingAs(User::factory()->create())->get($path);

        $response->assertOk();
        expect($response->headers->get('Content-Security-Policy'))->not->toBeNull($path);
    }
});

it('leaves no way to run script that did not come from this application', function () {
    $policy = contentSecurityPolicyOf('/');

    // This is the half of the policy that is worth having. An injected <script>,
    // or a src pointing anywhere else, must not run — so no escape hatch here,
    // whether written into script-src or inherited from default-src.
    expect($policy)->toHaveKey('script-src');
    expect($policy['script-src'])->toBe("'self'");
    expect($policy['default-src'])->toBe("'self'");
    expect($policy)->toHaveKey('object-src');
    expect($policy['object-src'])->toBe("'none'");
});

it('opens a frame only for the video host the article page uses after a click', function () {
    $policy = contentSecurityPolicyOf('/');

    expect($policy)->toHaveKey('frame-src');
    expect($policy['frame-src'])->toBe('https://www.youtube-nocookie.com');

    // The src the player actually builds must be inside what the policy allows,
    // otherwise clicking play would draw an empty box.
    expect(File::get(resource_path('js/Components/News/YoutubeEmbed.jsx')))
        ->toContain('https://www.youtube-nocookie.com/embed/');
});

it('does not let another site put the pages in a frame', function () {
    $policy = contentSecurityPolicyOf('/');

    expect($policy['frame-ancestors'])->toBe("'none'");
    expect($policy['base-uri'])->toBe("'self'");
    expect($policy['form-action'])->toBe("'self'");
});

it('keeps crawlers out of the admin', function () {
    // Served by the application, not as a file, so nothing in public/ can shadow
    // it with an older copy that allowed everything.
    expect(File::exists(public_path('robots.txt')))->toBeFalse();

    test()->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin', false);
});
