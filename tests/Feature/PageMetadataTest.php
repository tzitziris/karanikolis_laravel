<?php

use App\Models\Article;
use App\Models\User;
use App\Services\UploadedArticleImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

/**
 * Read the head the way a crawler does: from the HTML the server sent, with no
 * JavaScript run. Anything only the browser would assemble does not count.
 *
 * @return array<string, string>
 */
function headOf(string $path): array
{
    $html = test()->get($path)->getContent();

    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
    $xpath = new DOMXPath($document);

    $head = [];

    foreach ($xpath->query('//head/title') as $node) {
        $head['title'] = $node->textContent;
    }

    foreach ($xpath->query('//head/meta[@name or @property]') as $node) {
        $key = $node->getAttribute('name') ?: $node->getAttribute('property');
        $head[$key] = $node->getAttribute('content');
    }

    foreach ($xpath->query('//head/link[@rel="canonical"]') as $node) {
        $head['canonical'] = $node->getAttribute('href');
    }

    return $head;
}

function metadataCoverUpload(): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'share-cover-').'.jpg';
    $image = imagecreatetruecolor(1800, 1200);
    imagefilledrectangle($image, 0, 0, 1800, 1200, imagecolorallocate($image, 40, 40, 40));
    imagejpeg($image, $path, 88);
    imagedestroy($image);

    return new UploadedFile($path, 'cover.jpg', 'image/jpeg', null, true);
}

function articleWithCover(array $attributes = []): Article
{
    $cover = app(UploadedArticleImageService::class)->store(metadataCoverUpload(), 'cover');

    return Article::factory()->published()->create([
        'cover_image_height' => $cover['height'],
        'cover_image_name' => $cover['name'],
        'cover_image_width' => $cover['width'],
        ...$attributes,
    ]);
}

it('gives every public page its own Greek title and description', function () {
    $paths = ['/', '/coaches', '/schedule', '/news', '/about'];
    $heads = collect($paths)->mapWithKeys(fn (string $path): array => [$path => headOf($path)]);

    expect($heads->reject(fn (array $head): bool => str_contains($head['title'] ?? '', 'Μαχητές Ελευθερούπολης'))->keys()->all())
        ->toBe([], 'These pages have no Greek title.');

    expect($heads->reject(fn (array $head): bool => mb_strlen($head['description'] ?? '') > 60)->keys()->all())
        ->toBe([], 'These pages have no usable description.');

    expect($heads->filter(fn (array $head): bool => isset($head['robots']))->keys()->all())
        ->toBe([], 'These pages are being kept out of search results.');

    // One title for the whole site is what we started with, and it makes every
    // search result and every open tab look the same.
    $titles = $heads->map(fn (array $head): string => $head['title']);
    expect($titles->unique()->count())->toBe($titles->count(), 'Two pages share a title.');
});

it('describes an article with its own title, its own summary and its own cover', function () {
    $article = articleWithCover([
        'excerpt' => 'Δυνατές εμφανίσεις και καθαρό αγωνιστικό πλάνο για την ομάδα μας στην Καβάλα.',
        'title' => 'Αγωνιστική ημέρα στην Καβάλα',
    ]);

    $head = headOf("/news/{$article->slug}");

    expect($head['og:type'])->toBe('article');
    expect($head['og:title'])->toBe('Αγωνιστική ημέρα στην Καβάλα · Μαχητές Ελευθερούπολης');
    expect($head['og:description'])->toBe('Δυνατές εμφανίσεις και καθαρό αγωνιστικό πλάνο για την ομάδα μας στην Καβάλα.');
    expect($head['article:published_time'])->toBe($article->published_at->toISOString());
    expect($head['og:url'])->toBe(url("/news/{$article->slug}"));
    expect($head['canonical'])->toBe(url("/news/{$article->slug}"));
    expect($head['og:image'])->toContain($article->cover_image_name);
});

it('offers a share picture that is webp, sized, and actually on the disk', function () {
    $article = articleWithCover();

    foreach (["/news/{$article->slug}", '/', '/schedule'] as $path) {
        $head = headOf($path);
        $url = $head['og:image'] ?? '';

        expect(str_starts_with($url, 'http') && str_ends_with($url, '.webp'))
            ->toBeTrue("The share picture for {$path} is not an absolute webp address: {$url}");

        // A preview pointing at a missing file shows nothing, and the page
        // itself gives the owner no hint that anything is wrong.
        $file = public_path(ltrim((string) parse_url($url, PHP_URL_PATH), '/'));
        expect(File::exists($file))->toBeTrue("The share picture for {$path} is not on disk: {$file}");

        [$width, $height] = getimagesize($file);
        expect((int) $head['og:image:width'])->toBe($width, "Wrong declared width for {$path}.");
        expect((int) $head['og:image:height'])->toBe($height, "Wrong declared height for {$path}.");
        expect($width >= 1200)->toBeTrue("The share picture for {$path} is too small for a large card: {$width}px.");
    }
});

it('shares the cover an article actually has, uploaded or one of the site pictures', function () {
    // Every article written so far uses a picture that ships with the site. If
    // only uploads counted, all of them would share the same generic photograph.
    $withSitePicture = Article::factory()->published()->create(['cover_image_name' => 'athlete-kick']);

    expect(headOf("/news/{$withSitePicture->slug}")['og:image'])->toContain('athlete-kick-');
});

it('falls back to the school picture when an article has no cover', function () {
    $article = Article::factory()->published()->create(['cover_image_name' => null]);

    expect(headOf("/news/{$article->slug}")['og:image'])->toContain('hero-kickboxing');
});

it('keeps the login form out of search results', function () {
    expect(headOf('/admin/login')['robots'] ?? '')->toContain('noindex');
});

it('keeps the admin out of search results', function () {
    $article = Article::factory()->create();
    test()->actingAs(User::factory()->create());

    foreach (['/admin', "/admin/articles/{$article->id}/edit"] as $path) {
        $head = headOf($path);

        expect($head['robots'] ?? '')->toContain('noindex');
        expect(isset($head['canonical']))->toBeFalse("{$path} claims to be a page worth indexing.");
    }
});

it('does not offer a page that answered 404 to a search engine', function () {
    expect(headOf('/kati-pou-den-yparxei')['robots'] ?? '')->toContain('noindex');
});

it('separates the second page of the archive from the first', function () {
    Article::factory()->count(12)->published()->create();

    $first = headOf('/news');
    $second = headOf('/news?page=2');

    expect($second['title'])->not->toBe($first['title'])->toContain('Σελίδα 2');
    expect($second['canonical'])->toBe(url('/news').'?page=2');
});

it('writes the head on the server, where a crawler that runs no JavaScript reads it', function () {
    $article = Article::factory()->published()->create();

    // Not a style preference: Facebook's crawler never executes the bundle, so
    // a tag that only React adds does not exist as far as a shared link goes.
    $html = test()->get("/news/{$article->slug}")->getContent();
    $head = substr($html, 0, (int) strpos($html, '</head>'));

    foreach (['og:title', 'og:description', 'og:image', 'og:url', 'og:type'] as $property) {
        expect($head)->toContain('property="'.$property.'"');
    }
});
