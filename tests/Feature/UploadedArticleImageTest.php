<?php

use App\Exceptions\ArticleImageUploadException;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\User;
use App\Services\AdminArticleService;
use App\Services\UploadedArticleImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    File::deleteDirectory(public_path('images/uploads/articles'));
});

afterEach(function () {
    File::deleteDirectory(public_path('images/uploads/articles'));
});

function makeJpegUpload(int $width = 1800, int $height = 1200, string $mime = 'image/jpeg'): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'article-upload-').'.jpg';
    $image = imagecreatetruecolor($width, $height);

    for ($x = 0; $x < $width; $x += 120) {
        $colour = imagecolorallocate($image, ($x * 7) % 255, 90, 180);
        imagefilledrectangle($image, $x, 0, min($width, $x + 119), $height, $colour);
    }

    imagejpeg($image, $path, 88);
    imagedestroy($image);

    return new UploadedFile($path, 'owner-photo.jpg', $mime, null, true);
}

function uploadedImageTestBody(string $text): array
{
    return [
        'content' => [
            [
                'content' => [
                    [
                        'text' => $text,
                        'type' => 'text',
                    ],
                ],
                'type' => 'paragraph',
            ],
        ],
        'type' => 'doc',
    ];
}

function derivativePaths(string $name): array
{
    return File::glob(public_path("images/{$name}-*.webp")) ?: [];
}

it('stores an uploaded article cover only as role-sized webp derivatives with dimensions', function () {
    $upload = makeJpegUpload();
    $result = app(UploadedArticleImageService::class)->store($upload, 'cover');
    $expectedWidths = [480, 768, 1024, 1280, 1600];

    expect($result['name'])->toStartWith('uploads/articles/')
        ->and($result['width'])->toBe(1800)
        ->and($result['height'])->toBe(1200)
        ->and(collect($result['derivatives'])->pluck('width')->all())->toBe($expectedWidths);

    foreach ($result['derivatives'] as $derivative) {
        expect($derivative['path'])->toEndWith("-{$derivative['width']}.webp")
            ->and(File::exists($derivative['path']))->toBeTrue()
            ->and($derivative['bytes'])->toBeGreaterThan(0);
    }

    $publicOriginals = collect(File::allFiles(public_path('images/uploads/articles')))
        ->reject(fn (SplFileInfo $file): bool => $file->getExtension() === 'webp')
        ->map(fn (SplFileInfo $file): string => $file->getFilename())
        ->values();

    expect($publicOriginals)->toBeEmpty();
});

it('uses different uploaded derivative sizes for gallery photographs', function () {
    $upload = makeJpegUpload();
    $result = app(UploadedArticleImageService::class)->store($upload, 'gallery');

    expect(collect($result['derivatives'])->pluck('width')->all())
        ->toBe([320, 480, 768, 1024, 1280, 1600]);
});

it('accepts a full size twelve megapixel phone photograph comfortably', function () {
    $upload = makeJpegUpload(4032, 3024);
    $result = app(UploadedArticleImageService::class)->store($upload, 'cover');

    expect($result['width'])->toBe(4032)
        ->and($result['height'])->toBe(3024)
        ->and($result['source_bytes'])->toBeLessThanOrEqual(12 * 1024 * 1024)
        ->and(collect($result['derivatives'])->pluck('width')->all())->toBe([480, 768, 1024, 1280, 1600, 1920, 2400]);
});

it('refuses files whose declared type lies and leaves no uploaded files behind', function () {
    $upload = makeJpegUpload(mime: 'image/png');

    expect(fn () => app(UploadedArticleImageService::class)->store($upload, 'cover'))
        ->toThrow(ArticleImageUploadException::class, 'Ο τύπος του αρχείου δεν ταιριάζει με το περιεχόμενό του. Ανεβάστε την αρχική φωτογραφία ως JPEG ή PNG.');

    expect(File::exists(public_path('images/uploads/articles')))->toBeTrue()
        ->and(File::allFiles(public_path('images/uploads/articles')))->toBeEmpty();
});

it('refuses oversized uploads with a Greek owner-facing message and no leftovers', function () {
    config(['images.uploads.limits.max_bytes' => 512]);
    $upload = makeJpegUpload();

    expect(fn () => app(UploadedArticleImageService::class)->store($upload, 'cover'))
        ->toThrow(ArticleImageUploadException::class, 'Η φωτογραφία είναι πολύ μεγάλη. Ανεβάστε μικρότερο αρχείο.');

    expect(File::exists(public_path('images/uploads/articles')))->toBeTrue()
        ->and(File::allFiles(public_path('images/uploads/articles')))->toBeEmpty();
});

it('treats only site-made upload UUIDs as uploaded image names', function () {
    $service = app(UploadedArticleImageService::class);
    $staticPath = public_path('images/static/hero-kickboxing-320.webp');
    $originalHash = hash_file('sha256', $staticPath);

    $service->deleteDerivatives('uploads/articles/../../static/hero-kickboxing');

    expect($service->isUploadedName('uploads/articles/../../static/hero-kickboxing'))->toBeFalse()
        ->and($service->isUploadedName('uploads/articles/not-a-real-upload'))->toBeFalse()
        ->and($service->isUploadedName('uploads/articles/00000000-0000-4000-8000-000000000000'))->toBeTrue()
        ->and(File::exists($staticPath))->toBeTrue()
        ->and(hash_file('sha256', $staticPath))->toBe($originalHash);
});

it('replaces an article cover through its own request and deletes only the old unreferenced derivatives', function () {
    $article = Article::factory()->create([
        'body' => uploadedImageTestBody('Το κείμενο μένει όπως είναι.'),
        'title' => 'Άρθρο με εξώφυλλο',
    ]);

    $this->actingAs(User::factory()->create())
        ->post("/admin/articles/{$article->id}/cover", [
            'alt_text' => 'Πρώτη φωτογραφία εξωφύλλου',
            'photo' => makeJpegUpload(),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Η φωτογραφία εξωφύλλου αποθηκεύτηκε.');

    $article->refresh();
    $firstName = $article->cover_image_name;
    $firstPaths = derivativePaths($firstName);

    expect($firstPaths)->not->toBeEmpty()
        ->and($article->cover_image_alt_text)->toBe('Πρώτη φωτογραφία εξωφύλλου')
        ->and($article->body)->toBe(uploadedImageTestBody('Το κείμενο μένει όπως είναι.'));

    $this->actingAs(User::factory()->create())
        ->post("/admin/articles/{$article->id}/cover", [
            'alt_text' => 'Δεύτερη φωτογραφία εξωφύλλου',
            'photo' => makeJpegUpload(1600, 1000),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Η φωτογραφία εξωφύλλου αποθηκεύτηκε.');

    $article->refresh();
    $secondPaths = derivativePaths($article->cover_image_name);

    expect($article->cover_image_name)->not->toBe($firstName)
        ->and($article->cover_image_alt_text)->toBe('Δεύτερη φωτογραφία εξωφύλλου')
        ->and($article->cover_image_width)->toBe(1600)
        ->and($article->cover_image_height)->toBe(1000)
        ->and($article->body)->toBe(uploadedImageTestBody('Το κείμενο μένει όπως είναι.'))
        ->and($secondPaths)->not->toBeEmpty();

    foreach ($firstPaths as $path) {
        expect(File::exists($path))->toBeFalse("Old cover derivative remained on disk: {$path}");
    }
});

it('appends gallery uploads and preserves the owner order, descriptions and article text', function () {
    $article = Article::factory()->create([
        'body' => uploadedImageTestBody('Κείμενο που δεν ταξιδεύει με τις φωτογραφίες.'),
    ]);
    $user = User::factory()->create();

    foreach (['Πρώτη φωτογραφία', 'Δεύτερη φωτογραφία', 'Τρίτη φωτογραφία'] as $altText) {
        $this->actingAs($user)
            ->post("/admin/articles/{$article->id}/gallery", [
                'alt_text' => $altText,
                'photo' => makeJpegUpload(),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Η φωτογραφία προστέθηκε στη συλλογή.');
    }

    $images = $article->images()->get();

    expect($images->pluck('alt_text')->all())->toBe(['Πρώτη φωτογραφία', 'Δεύτερη φωτογραφία', 'Τρίτη φωτογραφία'])
        ->and($images->pluck('sort_order')->all())->toBe([0, 1, 2])
        ->and($article->refresh()->body)->toBe(uploadedImageTestBody('Κείμενο που δεν ταξιδεύει με τις φωτογραφίες.'));

    $this->actingAs($user)
        ->put("/admin/articles/{$article->id}/gallery", [
            'images' => [
                ['alt_text' => 'Τρίτη πρώτη στη σειρά', 'id' => $images[2]->id],
                ['alt_text' => 'Πρώτη δεύτερη στη σειρά', 'id' => $images[0]->id],
                ['alt_text' => 'Δεύτερη τρίτη στη σειρά', 'id' => $images[1]->id],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Η σειρά των φωτογραφιών αποθηκεύτηκε.');

    expect($article->images()->pluck('alt_text')->all())->toBe([
        'Τρίτη πρώτη στη σειρά',
        'Πρώτη δεύτερη στη σειρά',
        'Δεύτερη τρίτη στη σειρά',
    ])->and($article->images()->pluck('sort_order')->all())->toBe([0, 1, 2]);
});

it('removes a gallery image from the middle, deletes its files and compacts the remaining order', function () {
    $article = Article::factory()->create();
    $first = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'gallery');
    $second = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'gallery');
    $third = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'gallery');
    $article->images()->createMany([
        ['alt_text' => 'Πρώτη', 'height' => $first['height'], 'image_name' => $first['name'], 'sort_order' => 0, 'width' => $first['width']],
        ['alt_text' => 'Δεύτερη', 'height' => $second['height'], 'image_name' => $second['name'], 'sort_order' => 1, 'width' => $second['width']],
        ['alt_text' => 'Τρίτη', 'height' => $third['height'], 'image_name' => $third['name'], 'sort_order' => 2, 'width' => $third['width']],
    ]);
    $middle = $article->images()->where('image_name', $second['name'])->firstOrFail();
    $middlePaths = derivativePaths($second['name']);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/articles/{$article->id}/gallery/{$middle->id}")
        ->assertRedirect()
        ->assertSessionHas('success', 'Η φωτογραφία αφαιρέθηκε από τη συλλογή.');

    expect($article->images()->pluck('alt_text')->all())->toBe(['Πρώτη', 'Τρίτη'])
        ->and($article->images()->pluck('sort_order')->all())->toBe([0, 1]);

    foreach ($middlePaths as $path) {
        expect(File::exists($path))->toBeFalse("Removed gallery derivative remained on disk: {$path}");
    }

    expect(derivativePaths($first['name']))->not->toBeEmpty()
        ->and(derivativePaths($third['name']))->not->toBeEmpty();
});

it('returns a Greek photo message when PHP rejects the request before files are parsed', function () {
    $article = Article::factory()->create();

    $this->actingAs(User::factory()->create())
        ->call('POST', "/admin/articles/{$article->id}/cover", [], [], [], [
            'CONTENT_LENGTH' => (string) (13 * 1024 * 1024),
            'CONTENT_TYPE' => 'multipart/form-data',
        ], str_repeat('x', 13 * 1024 * 1024))
        ->assertStatus(413)
        ->assertSee('Η φωτογραφία είναι μεγαλύτερη από όσο δέχεται ο server. Ανεβάστε μικρότερη φωτογραφία.', false);
});

it('renders uploaded covers and gallery photos from database dimensions without article bodies in list data', function () {
    $cover = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'cover');
    $gallery = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'gallery');
    $article = Article::factory()->published()->create([
        'cover_image_height' => $cover['height'],
        'cover_image_name' => $cover['name'],
        'cover_image_width' => $cover['width'],
        'title' => 'Άρθρο με ανεβασμένες φωτογραφίες',
    ]);
    $image = ArticleImage::factory()->create([
        'article_id' => $article->id,
        'height' => $gallery['height'],
        'image_name' => $gallery['name'],
        'width' => $gallery['width'],
    ]);

    $this->get('/news')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('News')
            ->where('articles.0.coverImage.source', 'upload')
            ->where('articles.0.coverImage.name', $cover['name'])
            ->where('articles.0.coverImage.width', 1800)
            ->where('articles.0.coverImage.height', 1200)
            ->where('articles.0.coverImage.widths', [480, 768, 1024, 1280, 1600])
        );

    $this->get("/news/{$article->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Article')
            ->where('article.coverImage.source', 'upload')
            ->where('article.coverImage.name', $cover['name'])
            ->where('article.gallery.0.id', $image->id)
            ->where('article.gallery.0.image.source', 'upload')
            ->where('article.gallery.0.image.name', $gallery['name'])
            ->where('article.gallery.0.image.widths', [320, 480, 768, 1024, 1280, 1600])
        );
});

it('removes uploaded files after the article rows that point at them are gone', function () {
    $cover = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'cover');
    $gallery = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'gallery');
    $article = Article::factory()
        ->has(ArticleImage::factory()->state([
            'height' => $gallery['height'],
            'image_name' => $gallery['name'],
            'width' => $gallery['width'],
        ]), 'images')
        ->create([
            'cover_image_height' => $cover['height'],
            'cover_image_name' => $cover['name'],
            'cover_image_width' => $cover['width'],
            'title' => 'Άρθρο με αρχεία για διαγραφή',
        ]);

    $createdFiles = collect(File::allFiles(public_path('images/uploads/articles')))
        ->map(fn (SplFileInfo $file): string => $file->getPathname())
        ->values();

    expect($createdFiles)->not->toBeEmpty();

    $this->actingAs(User::factory()->create())
        ->delete("/admin/articles/{$article->id}")
        ->assertRedirect('/admin');

    foreach ($createdFiles as $path) {
        expect(File::exists($path))->toBeFalse("Uploaded derivative remained on disk: {$path}");
    }

    expect(File::allFiles(public_path('images/uploads/articles')))->toBeEmpty();
});

it('does not delete uploaded files while another database row still points at them', function () {
    $gallery = app(UploadedArticleImageService::class)->store(makeJpegUpload(), 'gallery');
    $first = Article::factory()->create();
    $second = Article::factory()->create();
    $firstImage = ArticleImage::factory()->create([
        'article_id' => $first->id,
        'height' => $gallery['height'],
        'image_name' => $gallery['name'],
        'width' => $gallery['width'],
    ]);
    ArticleImage::factory()->create([
        'article_id' => $second->id,
        'height' => $gallery['height'],
        'image_name' => $gallery['name'],
        'width' => $gallery['width'],
    ]);

    app(AdminArticleService::class)->deleteGalleryImage($firstImage);

    expect(File::glob(public_path("images/{$gallery['name']}-*.webp")))->not->toBeEmpty();
});
