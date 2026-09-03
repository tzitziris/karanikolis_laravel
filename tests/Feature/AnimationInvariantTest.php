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

it('does not ship stylesheet rules that hide JavaScript-revealed content', function () {
    $stylesheet = File::get(resource_path('css/app.css'));

    expect($stylesheet)->not->toContain('data-animation-hidden')
        ->and($stylesheet)->not->toContain('data-animation-managed')
        ->and($stylesheet)->not->toContain('data-reveal-item')
        ->and($stylesheet)->not->toContain('data-page-hero')
        ->and($stylesheet)->not->toContain('opacity: 0')
        ->and($stylesheet)->not->toContain('visibility: hidden')
        ->and($stylesheet)->not->toContain('display: none')
        ->and($stylesheet)->not->toContain('content-visibility: hidden');
});

it('keeps animation setup independent from font readiness and hidden waiting states', function () {
    $animationSource = File::get(resource_path('js/animation/pageAnimation.js'));
    $stylesheet = File::get(resource_path('css/app.css'));

    expect($animationSource)->not->toContain('window.setTimeout')
        ->and($animationSource)->not->toContain('forceRevealAll')
        ->and($animationSource)->not->toContain('document.fonts')
        ->and($animationSource)->not->toContain('fonts.ready')
        ->and($animationSource)->not->toContain('autoAlpha: 0')
        ->and($animationSource)->not->toContain('opacity: 0')
        ->and($animationSource)->not->toContain('visibility: hidden')
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
