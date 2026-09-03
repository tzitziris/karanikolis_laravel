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
    $manifestPath = storage_path('framework/testing/static-image-manifest.json');
    $outputDir = 'images/testing-static';

    File::deleteDirectory($sourceDir);
    File::deleteDirectory(public_path($outputDir));
    File::delete($manifestPath);

    writeImagePipelineTestJpeg("{$sourceDir}/demo.jpg");

    config()->set('images.static.source_dir', $sourceDir);
    config()->set('images.static.output_dir', $outputDir);
    config()->set('images.static.manifest_path', $manifestPath);
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
        ])->put('manifest', hash_file('sha256', $manifestPath))->all();

        $this->artisan('images:build-static')
            ->assertExitCode(0);

        $secondFiles = collect(File::files(public_path($outputDir)))
            ->map(fn (SplFileInfo $file): string => $file->getRealPath())
            ->sort()
            ->values();
        $secondHashes = $secondFiles->mapWithKeys(fn (string $path): array => [
            basename($path) => hash_file('sha256', $path),
        ])->put('manifest', hash_file('sha256', $manifestPath))->all();

        expect($secondFiles)->toHaveCount(2)
            ->and($secondHashes)->toBe($firstHashes);
    } finally {
        File::deleteDirectory($sourceDir);
        File::deleteDirectory(public_path($outputDir));
        File::delete($manifestPath);
    }
});

it('keeps static source photographs inside this repository and outside public paths', function () {
    $sourceDir = realpath(config('images.static.source_dir'));

    expect($sourceDir)->not->toBeFalse()
        ->and($sourceDir)->toStartWith(base_path())
        ->and($sourceDir)->not->toStartWith(public_path())
        ->and(config('images.static.source_dir'))->not->toContain('karanikolis_site')
        ->and(File::allFiles($sourceDir))->toHaveCount(14);
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

it('keeps the generated JavaScript static image manifest exactly aligned with local sources', function () {
    $manifest = json_decode(
        File::get(resource_path('js/images/staticImages.generated.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $photos = config('images.static.photos');
    $expectedImages = [];

    foreach ($photos as $name => $filename) {
        $info = getimagesize(config('images.static.source_dir')."/{$filename}");

        $expectedImages[$name] = [
            'height' => $info[1],
            'width' => $info[0],
            'widths' => array_values(array_filter(
                config('images.static.widths'),
                fn (int $width): bool => $width <= $info[0],
            )),
        ];
    }

    ksort($expectedImages);

    expect($manifest)->toBe([
        'basePath' => '/images/static',
        'images' => $expectedImages,
        'widths' => config('images.static.widths'),
    ]);
});

it('keeps SiteImage from throwing away the page on bad image input', function () {
    $source = File::get(resource_path('js/Components/SiteImage.jsx'));
    $manifestBridge = File::get(resource_path('js/images/staticImages.js'));

    expect($source)->not->toContain('throw new Error')
        ->and($source)->toContain('data-missing-static-image')
        ->and($source)->toContain('STATIC_IMAGE_LOADING[slot]')
        ->and($manifestBridge)->toContain("hero: 'eager'");
});
