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

function writeImagePipelineTestPng(string $path, int $width = 80, int $height = 120): void
{
    File::ensureDirectoryExists(dirname($path));

    $image = imagecreatetruecolor($width, $height);

    if (! $image instanceof GdImage) {
        throw new RuntimeException('Unable to create test mark.');
    }

    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    $accent = imagecolorallocatealpha($image, 212, 161, 66, 0);
    imagefill($image, 0, 0, $transparent);
    imagefilledellipse($image, (int) floor($width / 2), (int) floor($height / 2), $width - 8, $height - 8, $accent);

    if (! imagepng($image, $path)) {
        imagedestroy($image);

        throw new RuntimeException('Unable to write test mark.');
    }

    imagedestroy($image);
}

it('converts static images through an idempotent local command', function () {
    $sourceDir = storage_path('framework/testing/static-image-source');
    $markSourceDir = storage_path('framework/testing/static-mark-source');
    $manifestPath = storage_path('framework/testing/static-image-manifest.json');
    $outputDir = 'images/testing-static';

    File::deleteDirectory($sourceDir);
    File::deleteDirectory($markSourceDir);
    File::deleteDirectory(public_path($outputDir));
    File::delete($manifestPath);

    writeImagePipelineTestJpeg("{$sourceDir}/demo.jpg");
    writeImagePipelineTestPng("{$markSourceDir}/mark.png");

    config()->set('images.static.source_dir', $sourceDir);
    config()->set('images.static.mark_source_dir', $markSourceDir);
    config()->set('images.static.output_dir', $outputDir);
    config()->set('images.static.manifest_path', $manifestPath);
    config()->set('images.static.photos', ['demo' => 'demo.jpg']);
    config()->set('images.static.marks', ['mark' => 'mark.png']);
    config()->set('images.static.widths', [320, 480, 768]);
    config()->set('images.static.mark_widths', [32, 64]);

    try {
        $this->artisan('images:build-static')
            ->assertExitCode(0);

        $files = collect(File::files(public_path($outputDir)))
            ->map(fn (SplFileInfo $file): string => $file->getRealPath())
            ->sort()
            ->values();

        expect($files)->toHaveCount(4)
            ->and($files->map(fn (string $path): string => basename($path))->all())->toBe([
                'demo-320.webp',
                'demo-480.webp',
                'mark-32.webp',
                'mark-64.webp',
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

        expect($secondFiles)->toHaveCount(4)
            ->and($secondHashes)->toBe($firstHashes);
    } finally {
        File::deleteDirectory($sourceDir);
        File::deleteDirectory($markSourceDir);
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

it('keeps static source marks inside this repository and outside public paths', function () {
    $sourceDir = realpath(config('images.static.mark_source_dir'));

    expect($sourceDir)->not->toBeFalse()
        ->and($sourceDir)->toStartWith(base_path())
        ->and($sourceDir)->not->toStartWith(public_path())
        ->and(config('images.static.mark_source_dir'))->not->toContain('karanikolis_site')
        ->and(File::allFiles($sourceDir))->toHaveCount(1);
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

it('has generated transparent webp derivatives for every configured static mark', function () {
    $marks = config('images.static.marks');
    $widths = config('images.static.mark_widths');
    $sourceDir = config('images.static.mark_source_dir');
    $outputDir = trim(config('images.static.output_dir'), '/');

    expect($marks)->toBe(['site-logo' => 'logo.png'])
        ->and($widths)->toBe([32, 48, 64, 96, 128]);

    foreach ($marks as $name => $filename) {
        $source = "{$sourceDir}/{$filename}";
        $sourceInfo = getimagesize($source);

        expect($sourceInfo)->toBeArray("Missing source mark for {$name}.")
            ->and($sourceInfo['mime'] ?? null)->toBe('image/png');

        foreach ($widths as $width) {
            if ($width > $sourceInfo[0]) {
                continue;
            }

            $path = public_path("{$outputDir}/{$name}-{$width}.webp");
            $info = is_file($path) ? getimagesize($path) : false;
            $webp = is_file($path) ? imagecreatefromwebp($path) : false;

            expect(is_file($path))->toBeTrue("Missing WebP mark derivative: {$path}")
                ->and($info)->toBeArray("Invalid WebP mark derivative: {$path}")
                ->and((int) $info[0])->toBe($width)
                ->and($info['mime'] ?? null)->toBe('image/webp')
                ->and($webp)->toBeInstanceOf(GdImage::class);

            $alpha = (imagecolorat($webp, 0, 0) & 0x7F000000) >> 24;
            imagedestroy($webp);

            expect($alpha)->toBeGreaterThan(0, "WebP mark lost transparency: {$path}");
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
    $marks = config('images.static.marks');
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

    foreach ($marks as $name => $filename) {
        $info = getimagesize(config('images.static.mark_source_dir')."/{$filename}");

        $expectedImages[$name] = [
            'height' => $info[1],
            'width' => $info[0],
            'widths' => array_values(array_filter(
                config('images.static.mark_widths'),
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

it('lets exactly one home page photograph ask for priority loading', function () {
    $source = File::get(resource_path('js/Components/SiteImage.jsx'));
    $home = File::get(resource_path('js/Pages/Home.jsx'));

    expect($source)->toContain('priority = false')
        ->and($source)->toContain('fetchPriority={fetchPriority}')
        ->and($source)->toContain('const loadingMode = priority')
        ->and(substr_count($home, 'priority'))->toBe(1)
        ->and($home)->toContain('data-home-hero-image')
        ->and($home)->toContain('image="hero-kickboxing"');
});
