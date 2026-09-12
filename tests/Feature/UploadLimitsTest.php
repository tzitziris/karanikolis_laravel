<?php

use App\Models\Article;
use App\Models\User;
use App\Support\UploadLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/**
 * @return array<int, string>
 */
function photoErrorsFor(int $uploadError): array
{
    $article = Article::factory()->create();
    $path = sys_get_temp_dir().'/'.uniqid('upload-limit-', true).'.jpg';
    $image = imagecreatetruecolor(64, 48);
    imagejpeg($image, $path, 80);
    imagedestroy($image);

    $response = test()
        ->actingAs(User::factory()->create())
        ->from("/admin/articles/{$article->id}/edit")
        ->post("/admin/articles/{$article->id}/cover", [
            'alt_text' => 'δοκιμή',
            'photo' => new UploadedFile($path, 'photo.jpg', 'image/jpeg', $uploadError, true),
        ]);

    $errors = $response->baseResponse->getSession()?->get('errors');

    return is_array($errors)
        ? ($errors['default']['messages']['photo'] ?? [])
        : [];
}

it('tells the owner in Greek about every upload PHP breaks before we see it', function (int $uploadError) {
    $messages = photoErrorsFor($uploadError);

    expect($messages)->toHaveCount(1);

    $message = $messages[0];

    // The framework's own sentences are English and say nothing useful to this owner.
    expect($message)
        ->not->toContain('The photo')
        ->not->toContain('failed to upload')
        ->not->toContain('must not be greater')
        ->and(preg_match('/\p{Greek}/u', $message))->toBe(1);
})->with([
    UPLOAD_ERR_INI_SIZE,
    UPLOAD_ERR_FORM_SIZE,
    UPLOAD_ERR_PARTIAL,
    UPLOAD_ERR_NO_FILE,
    UPLOAD_ERR_NO_TMP_DIR,
    UPLOAD_ERR_CANT_WRITE,
    UPLOAD_ERR_EXTENSION,
]);

it('says how large a photograph may be when it was refused for being too large', function () {
    $label = UploadLimits::articleImageMaxLabel();

    expect(photoErrorsFor(UPLOAD_ERR_INI_SIZE)[0])->toContain($label)
        ->and(photoErrorsFor(UPLOAD_ERR_FORM_SIZE)[0])->toContain($label);
});

it('does not blame the size when no photograph was chosen at all', function () {
    expect(photoErrorsFor(UPLOAD_ERR_NO_FILE)[0])->toBe('Διαλέξτε φωτογραφία για ανέβασμα.');
});

it('never promises to accept more than this PHP will actually take', function () {
    $enforced = UploadLimits::articleImageMaxBytes();

    expect($enforced)->toBeLessThanOrEqual(UploadLimits::iniBytes('upload_max_filesize'))
        ->and($enforced)->toBeLessThanOrEqual(UploadLimits::iniBytes('post_max_size'))
        ->and($enforced)->toBeLessThanOrEqual((int) config('images.uploads.limits.max_bytes'))
        ->and($enforced)->toBeGreaterThan(0);
});

it('reads the size suffixes PHP writes its limits with', function () {
    expect(UploadLimits::iniBytes('upload_max_filesize'))->toBe((int) (((float) ini_get('upload_max_filesize')) * 1024 * 1024));
});

it('writes a whole number of megabytes without a decimal', function () {
    // 10485760 / 1048576 is an int in PHP, so an identity check against floor() silently fails.
    expect(UploadLimits::articleImageMaxLabel())->toMatch('/^\d+(,\d)? MB$/');

    $megabytes = UploadLimits::articleImageMaxBytes() / 1048576;

    if (floor((float) $megabytes) == $megabytes) {
        expect(UploadLimits::articleImageMaxLabel())->not->toContain(',');
    }
});

it('tells the owner the limit before they choose a file', function () {
    $article = Article::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get("/admin/articles/{$article->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ArticleForm')
            ->where('uploadLimits.maxBytes', UploadLimits::articleImageMaxBytes())
            ->where('uploadLimits.maxLabel', UploadLimits::articleImageMaxLabel())
        );
});
