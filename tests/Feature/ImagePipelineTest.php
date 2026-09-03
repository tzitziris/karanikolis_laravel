<?php

use Illuminate\Support\Facades\File;

function writeImagePipelineTestJpeg(string $path, int $width = 640, int $height = 426): void
{
    File::ensureDirectoryExists(dirname($path));

    $image = imagecreatetruecolor($width, $height);

    if (! $image instanceof GdImage) {
        throw new RuntimeException('Unable to create test image.');
    }

    $background = imagecolorallocate($image, 18, 18, 18);
    $accent = imagecolorallocate($image, 212, 161, 66);
    imagefill($image, 0, 0, $background);
    imagefilledrectangle($image, 0, 0, (int) floor($width / 2), $height, $accent);

    if (! imagejpeg($image, $path, 90)) {
        imagedestroy($image);

        throw new RuntimeException('Unable to write test image.');
    }

    imagedestroy($image);
}

it('converts static images through an idempotent local command', function () {
    $sourceDir = storage_path('framework/testing/static-image-source');
    $outputDir = 'images/testing-static';

    File::deleteDirectory($sourceDir);
    File::deleteDirectory(public_path($outputDir));

    writeImagePipelineTestJpeg("{$sourceDir}/demo.jpg");

    config()->set('images.static.source_dir', $sourceDir);
    config()->set('images.static.output_dir', $outputDir);
    config()->set('images.static.photos', ['demo' => 'demo.jpg']);
    config()->set('images.static.widths', [320, 480, 768]);

    try {
        $this->artisan('images:build-static')
            ->assertExitCode(0);

        $files = collect(File::files(public_path($outputDir)))
            ->map(fn (SplFileInfo $file): string => $file->getRealPath())
            ->sort()
            ->values();

        expect($files)->toHaveCount(2)
            ->and($files->map(fn (string $path): string => basename($path))->all())->toBe([
                'demo-320.webp',
                'demo-480.webp',
            ]);

        $firstHashes = $files->mapWithKeys(fn (string $path): array => [
            basename($path) => hash_file('sha256', $path),
        ])->all();

        $this->artisan('images:build-static')
            ->assertExitCode(0);

        $secondFiles = collect(File::files(public_path($outputDir)))
            ->map(fn (SplFileInfo $file): string => $file->getRealPath())
            ->sort()
            ->values();
        $secondHashes = $secondFiles->mapWithKeys(fn (string $path): array => [
            basename($path) => hash_file('sha256', $path),
        ])->all();

        expect($secondFiles)->toHaveCount(2)
            ->and($secondHashes)->toBe($firstHashes);
    } finally {
        File::deleteDirectory($sourceDir);
        File::deleteDirectory(public_path($outputDir));
    }
});

it('has generated webp derivatives for every configured static photograph', function () {
    $photos = config('images.static.photos');
    $widths = config('images.static.widths');
    $sourceDir = config('images.static.source_dir');
    $outputDir = trim(config('images.static.output_dir'), '/');

    expect($photos)->toHaveCount(14);

    foreach ($photos as $name => $filename) {
        $source = "{$sourceDir}/{$filename}";
        $sourceInfo = getimagesize($source);

        expect($sourceInfo)->toBeArray("Missing source image for {$name}.");

        foreach ($widths as $width) {
            if ($width > $sourceInfo[0]) {
                continue;
            }

            $path = public_path("{$outputDir}/{$name}-{$width}.webp");
            $info = is_file($path) ? getimagesize($path) : false;

            expect(is_file($path))->toBeTrue("Missing WebP derivative: {$path}")
                ->and($info)->toBeArray("Invalid WebP derivative: {$path}")
                ->and((int) $info[0])->toBe($width)
                ->and($info['mime'] ?? null)->toBe('image/webp');
        }
    }
});

it('keeps original bitmap formats out of browser-reachable public paths', function () {
    $forbiddenExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif', 'tiff', 'avif'];
    $publicBitmaps = collect(File::allFiles(public_path()))
        ->filter(fn (SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), $forbiddenExtensions, true))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($publicBitmaps)->toBe([]);
});

it('keeps direct image file references inside the SiteImage gateway component', function () {
    $scannedFiles = collect([
        ...File::allFiles(resource_path('js/Components')),
        ...File::allFiles(resource_path('js/Pages')),
    ])->reject(fn (SplFileInfo $file): bool => $file->getRelativePathname() === 'SiteImage.jsx');

    foreach ($scannedFiles as $file) {
        $source = File::get($file->getPathname());

        expect($source)->not->toContain('<img')
            ->and($source)->not->toMatch('/\\.(jpe?g|png|gif|webp|avif)([?\'"`)]|\\s)/i')
            ->and($source)->not->toContain('/images/')
            ->and($source)->not->toContain('public/');
    }

    foreach (File::allFiles(resource_path('views')) as $file) {
        $source = File::get($file->getPathname());

        expect($source)->not->toContain('<img')
            ->and($source)->not->toContain('/images/');
    }
});

it('keeps bitmap URLs out of stylesheets', function () {
    $stylesheet = File::get(resource_path('css/app.css'));

    expect($stylesheet)->not->toMatch('/url\\([^)]*\\.(jpe?g|png|gif|webp|avif)/i');
});

it('keeps the JavaScript static image manifest aligned with PHP configuration', function () {
    $componentManifest = File::get(resource_path('js/images/staticImages.js'));
    $photos = config('images.static.photos');

    expect($componentManifest)->toContain('STATIC_IMAGE_WIDTHS = [320, 480, 768, 1024, 1280, 1600, 1920, 2400]');

    foreach ($photos as $name => $filename) {
        $info = getimagesize(config('images.static.source_dir')."/{$filename}");

        expect($componentManifest)->toContain("'{$name}':")
            ->and($componentManifest)->toContain("height: {$info[1]}")
            ->and($componentManifest)->toContain("width: {$info[0]}");
    }
});
