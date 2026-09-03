<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

function frontendFiles(): Collection
{
    return collect(File::allFiles(resource_path('js')));
}

it('uses GSAP as the only animation runtime dependency', function () {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $dependencies = array_keys($package['dependencies'] ?? []);
    $devDependencies = array_keys($package['devDependencies'] ?? []);
    $allDependencies = [...$dependencies, ...$devDependencies];

    expect($dependencies)->toContain('gsap')
        ->and($allDependencies)->not->toContain('@gsap/react')
        ->and($allDependencies)->not->toContain('framer-motion')
        ->and($allDependencies)->not->toContain('motion')
        ->and($allDependencies)->not->toContain('@motionone/dom')
        ->and($allDependencies)->not->toContain('@motionone/react');
});

it('renders readable fallback content outside JavaScript bootstrapping', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('id="readable-fallback"', false)
        ->assertSee('Προσωρινή σελίδα')
        ->assertSee('Η εφαρμογή Laravel είναι έτοιμη.')
        ->assertSee('Πρώτη ανάγνωση')
        ->assertSee('Κύλιση')
        ->assertSee('Καθαρό κλείσιμο');

    $html = $response->getContent();

    expect($html)->not->toContain('<noscript')
        ->and($html)->not->toContain('readable-fallback hidden')
        ->and($html)->not->toContain('readable-fallback opacity-0')
        ->and($html)->not->toContain('readable-fallback invisible')
        ->and($html)->not->toContain('aria-hidden="true"');
});

it('requires Inertia pages to provide server-readable fallback content', function () {
    $phpFiles = collect([
        ...File::allFiles(app_path('Http/Controllers')),
        ...File::allFiles(base_path('routes')),
    ]);

    foreach ($phpFiles as $file) {
        $source = File::get($file->getPathname());

        expect($source)->not->toContain('Inertia::render(')
            ->and($source)->not->toContain('inertia(');
    }

    $supportSource = File::get(app_path('Support/ReadablePage.php'));

    expect($supportSource)->toContain("'readableFallback' => \$fallback")
        ->and($supportSource)->toContain('throw new InvalidArgumentException');
});

it('keeps animation setup independent from font readiness and protected by a watchdog reveal', function () {
    $animationSource = File::get(resource_path('js/animation/pageAnimation.js'));
    $stylesheet = File::get(resource_path('css/app.css'));

    expect($animationSource)->toContain('REVEAL_WATCHDOG_MS')
        ->and($animationSource)->toContain('window.setTimeout')
        ->and($animationSource)->toContain('forceRevealAll(root)')
        ->and($animationSource)->not->toContain('document.fonts')
        ->and($animationSource)->not->toContain('fonts.ready')
        ->and($stylesheet)->not->toContain('[data-reveal-item] {')
        ->and($stylesheet)->not->toContain('opacity: 0')
        ->and($stylesheet)->not->toContain('visibility: hidden');
});

it('forces scroll-trigger animations through a page-scoped cleanup helper', function () {
    $animationSource = File::get(resource_path('js/animation/pageAnimation.js'));

    expect($animationSource)->toContain('gsap.context')
        ->and($animationSource)->toContain('context?.revert()')
        ->and($animationSource)->toContain('ScrollTrigger.getAll().length')
        ->and($animationSource)->toContain('getReducedMotionPreference()');

    frontendFiles()
        ->reject(fn (SplFileInfo $file) => str_starts_with($file->getRelativePathname(), 'animation/'))
        ->each(function (SplFileInfo $file) {
            $source = File::get($file->getPathname());

            expect($source)->not->toContain("from 'gsap'")
                ->and($source)->not->toContain('from "gsap"')
                ->and($source)->not->toContain('gsap/ScrollTrigger')
                ->and($source)->not->toContain('ScrollTrigger.create')
                ->and($source)->not->toContain('scrollTrigger:');
        });
});
