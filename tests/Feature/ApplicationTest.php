<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('serves the home page through Inertia', function () {
    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('articles', [])
        );
});

it('serves the motion demo as a separate Inertia page component', function () {
    $this->get('/dokimi-kinisis')
        ->assertInertia(fn (Assert $page) => $page
            ->component('MotionDemo')
            ->where('message', 'Η δοκιμαστική δεύτερη διαδρομή είναι έτοιμη.')
        );
});

it('serves temporary public pages for every shell navigation target', function (string $path, string $title) {
    $this->get($path)
        ->assertInertia(fn (Assert $page) => $page
            ->component('PublicPlaceholder')
            ->where('eyebrow', 'Προσωρινή σελίδα')
            ->where('title', $title)
            ->has('message')
        );
})->with([
    ['/coaches', 'Ομάδα'],
    ['/schedule', 'Πρόγραμμα'],
    ['/news', 'Νέα'],
    ['/about', 'Σχετικά'],
]);

it('keeps the public shell outside the keyed Inertia page component', function () {
    $entry = File::get(resource_path('js/app.jsx'));
    $shell = File::get(resource_path('js/Layouts/SiteShell.jsx'));
    $navbar = File::get(resource_path('js/Components/Navbar.jsx'));

    expect($entry)->toContain('<SiteShell>')
        ->and($entry)->toContain('<Component key={key} {...props} />')
        ->and($shell)->toContain('<Navbar />')
        ->and($shell)->toContain('<main id="site-content">{children}</main>')
        ->and($shell)->toContain('<Footer />')
        ->and($navbar)->toContain("window.addEventListener('scroll', onScroll")
        ->and($navbar)->toContain('data-site-header');
});

it('does not leave unrendered page components behind', function () {
    $pageComponents = collect(File::files(resource_path('js/Pages')))
        ->map(fn (SplFileInfo $file): string => $file->getFilename())
        ->sort()
        ->values()
        ->all();

    expect($pageComponents)->toBe([
        'Home.jsx',
        'MotionDemo.jsx',
        'PublicPlaceholder.jsx',
    ]);
});

it('makes the mobile navigation a real dialog with inert page content and resilient animation', function () {
    $navbar = File::get(resource_path('js/Components/Navbar.jsx'));
    $animation = File::get(resource_path('js/animation/pageAnimation.js'));

    expect($navbar)->toContain('role="dialog"')
        ->and($navbar)->toContain('aria-modal="true"')
        ->and($navbar)->toContain("appRoot?.setAttribute('inert', '')")
        ->and($navbar)->toContain("appRoot?.removeAttribute('inert')")
        ->and($navbar)->toContain("event.key === 'Escape'")
        ->and($navbar)->toContain("event.key !== 'Tab'")
        ->and($navbar)->toContain('opener?.focus({ preventScroll: true })')
        ->and($animation)->toContain('export function animateMobileMenuOpen')
        ->and($animation)->toContain('catch (error)')
        ->and($animation)->toContain('return () => {}');
});

it('runs the test suite against the dedicated MariaDB database', function () {
    $connection = DB::connection();
    $testDatabase = config('database.connections.mariadb.database');
    $developmentDatabase = config('database.connections.mariadb.development_database');
    $version = strtolower($connection->selectOne('select version() as version')->version);

    expect(config('database.default'))->toBe('mariadb')
        ->and($connection->getDatabaseName())->not->toBe($developmentDatabase)
        ->and($connection->getDatabaseName())->toBe($testDatabase)
        ->and($connection->getDriverName())->toBe('mariadb')
        ->and($version)->toContain('mariadb');
});

it('creates a user through the model factory on the test database', function () {
    $user = User::factory()->create();
    $testDatabase = config('database.connections.mariadb.database');

    expect($user->exists)->toBeTrue()
        ->and($user->getConnection()->getDatabaseName())->toBe($testDatabase);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
    ]);
});

it('keeps server-side rendering out of the frontend pipeline', function () {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $scripts = implode(' ', $package['scripts'] ?? []);
    $dependencies = array_keys($package['dependencies'] ?? []);
    $devDependencies = array_keys($package['devDependencies'] ?? []);
    $ssrEntries = File::glob(resource_path('js/ssr.*'));

    expect(config('inertia.ssr.enabled'))->toBeFalse()
        ->and($scripts)->not->toContain('ssr')
        ->and($dependencies)->not->toContain('@inertiajs/server')
        ->and($devDependencies)->not->toContain('@inertiajs/server')
        ->and($ssrEntries)->toBe([]);
});

it('references only font files that exist in the public font directory', function () {
    $css = File::get(resource_path('css/app.css'));
    preg_match_all('/url\\("(?<path>\\/fonts\\/[^"]+\\.woff2)"\\)/', $css, $matches);

    expect($matches['path'])->not->toBeEmpty();

    foreach ($matches['path'] as $fontPath) {
        expect(File::exists(public_path(ltrim($fontPath, '/'))))
            ->toBeTrue("Missing font file referenced by stylesheet: {$fontPath}");
    }
});

it('keeps fonts local, optional, and independent from JavaScript readiness', function () {
    $css = File::get(resource_path('css/app.css'));
    $scripts = collect(File::allFiles(resource_path('js')))
        ->map(fn (SplFileInfo $file) => File::get($file->getPathname()))
        ->implode("\n");
    $lowercaseCss = strtolower($css);

    expect($css)->toContain('font-display: optional')
        ->and($css)->not->toContain('font-display: swap')
        ->and($css)->not->toContain('fonts.gstatic.com')
        ->and($css)->not->toContain('fonts.googleapis.com')
        ->and($css)->not->toContain('Bebas Neue')
        ->and($lowercaseCss)->not->toContain('bebas')
        ->and($css)->toContain('--font-body-face')
        ->and($css)->toContain('--font-display-face')
        ->and($css)->toContain('--font-code-face')
        ->and($scripts)->not->toContain('document.fonts')
        ->and($scripts)->not->toContain('fonts.ready');
});

it('requires every shipped font family to have a metric-matched fallback face', function () {
    $css = File::get(resource_path('css/app.css'));
    preg_match_all('/@font-face\s*\{(?<body>.*?)\}/s', $css, $matches);

    $faces = collect($matches['body'])
        ->map(function (string $body) {
            preg_match_all('/(?<property>[-a-z]+)\s*:\s*(?<value>[^;]+);/i', $body, $declarations);

            $properties = collect($declarations['property'])
                ->mapWithKeys(fn (string $property, int $index) => [
                    strtolower($property) => trim($declarations['value'][$index]),
                ]);

            $family = trim($properties->get('font-family', ''), " \t\n\r\0\x0B\"'");

            return [
                'family' => $family,
                'style' => $properties->get('font-style', 'normal'),
                'weight' => preg_replace('/\s+/', ' ', $properties->get('font-weight', '400')),
                'properties' => $properties,
            ];
        });

    $shippedFaces = $faces
        ->filter(fn (array $face) => str_contains($face['properties']->get('src', ''), 'url("/fonts/'))
        ->reject(fn (array $face) => str_ends_with($face['family'], ' Fallback'));

    expect($shippedFaces)->not->toBeEmpty();

    foreach ($shippedFaces as $face) {
        $fallback = $faces->first(fn (array $candidate) => $candidate['family'] === "{$face['family']} Fallback"
            && $candidate['style'] === $face['style']
            && $candidate['weight'] === $face['weight']);

        expect($css)->toContain("\"{$face['family']}\", \"{$face['family']} Fallback\"");

        expect($fallback)->not->toBeNull("Missing metric-matched fallback for {$face['family']} {$face['weight']}.")
            ->and($fallback['properties']->get('src', ''))->toContain('local(')
            ->and($fallback['properties']->get('src', ''))->not->toContain('url(');

        foreach (['size-adjust', 'ascent-override', 'descent-override', 'line-gap-override'] as $metricDescriptor) {
            expect($fallback['properties']->has($metricDescriptor))
                ->toBeTrue("Missing {$metricDescriptor} on {$fallback['family']}.");
        }
    }
});

it('keeps font preloads family-complete and aligned with shipped font files', function () {
    $css = File::get(resource_path('css/app.css'));
    $blade = File::get(resource_path('views/app.blade.php'));

    preg_match_all('/@font-face\s*\{(?<body>.*?)\}/s', $css, $fontFaceMatches);
    preg_match_all('/<link\s+(?<attributes>[^>]+)>/i', $blade, $linkMatches);

    $fontFaces = collect($fontFaceMatches['body'])
        ->map(function (string $body) {
            preg_match_all('/(?<property>[-a-z]+)\s*:\s*(?<value>[^;]+);/i', $body, $declarations);

            $properties = collect($declarations['property'])
                ->mapWithKeys(fn (string $property, int $index) => [
                    strtolower($property) => trim($declarations['value'][$index]),
                ]);

            preg_match('/url\("(?<path>\/fonts\/[^"]+\.woff2)"\)/', $properties->get('src', ''), $pathMatch);

            return [
                'family' => trim($properties->get('font-family', ''), " \t\n\r\0\x0B\"'"),
                'display' => $properties->get('font-display'),
                'path' => $pathMatch['path'] ?? null,
                'unicodeRange' => $properties->get('unicode-range', ''),
            ];
        })
        ->filter(fn (array $face) => $face['path'] !== null);

    $fontFaces->each(fn (array $face) => expect($face['display'] ?? null)
        ->toBe('optional', "Shipped font must not swap after first paint: {$face['path']}."));

    $preloadedFontPaths = collect($linkMatches['attributes'])
        ->map(function (string $tag) {
            preg_match_all('/(?<name>[-:a-z]+)(?:\s*=\s*(?:"(?<double>[^"]*)"|\'(?<single>[^\']*)\'|(?<bare>[^\s"\'=<>`]+)))?/i', $tag, $attributeMatches, PREG_SET_ORDER);

            return collect($attributeMatches)
                ->mapWithKeys(fn (array $attribute) => [
                    strtolower($attribute['name']) => ($attribute['double'] ?? '') ?: (($attribute['single'] ?? '') ?: (($attribute['bare'] ?? '') ?: true)),
                ]);
        })
        ->filter(fn ($attributes) => ($attributes->get('rel') === 'preload') && ($attributes->get('as') === 'font'))
        ->map(function ($attributes) {
            expect($attributes->get('type'))->toBe('font/woff2')
                ->and($attributes->has('crossorigin'))->toBeTrue();

            return $attributes->get('href');
        })
        ->filter(fn ($href) => is_string($href) && str_starts_with($href, '/fonts/') && str_ends_with($href, '.woff2'))
        ->unique()
        ->filter()
        ->sort()
        ->values()
        ->all();

    $shippedFontPaths = $fontFaces
        ->pluck('path')
        ->sort()
        ->values()
        ->all();

    expect($preloadedFontPaths)->toBe($shippedFontPaths);

    $fontFaces
        ->groupBy('family')
        ->each(function ($familyFaces, string $family) use ($preloadedFontPaths) {
            $fontDisplays = $familyFaces->pluck('display')->unique()->values();
            $criticalStates = $familyFaces
                ->map(fn (array $face) => in_array($face['path'], $preloadedFontPaths, true))
                ->unique()
                ->values();

            expect($fontDisplays)->toHaveCount(1, "Font-display differs between slices of {$family}.")
                ->and($criticalStates)->toHaveCount(1, "Only part of {$family}'s coverage is fetched early.");
        });
});
