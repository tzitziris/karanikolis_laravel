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
