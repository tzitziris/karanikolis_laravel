<?php

use Illuminate\Support\Facades\File;

function stylesheetSource(): string
{
    return File::get(resource_path('css/app.css'));
}

function cssBlock(string $source, string $selector): string
{
    preg_match('/'.preg_quote($selector, '/').'\s*\{(?<body>.*?)\}/s', $source, $match);

    return $match['body'] ?? '';
}

function themeBlock(string $source): string
{
    preg_match('/@theme\s+inline\s*\{(?<body>.*?)\}/s', $source, $match);

    return $match['body'] ?? '';
}

/**
 * The one stylesheet Vite built. Asking it whether a class exists is the only
 * answer that matches what a visitor's browser will do; guessing from the name
 * of the class is what this file used to do, and it needed a hand-kept list of
 * exceptions to stop it calling `text-center` a colour.
 */
function compiledStylesheet(): string
{
    $files = File::glob(public_path('build/assets/*.css'));

    expect($files)->not->toBeEmpty('There is no compiled stylesheet. Run: npm run build');

    return collect($files)->map(fn (string $path): string => File::get($path))->implode("\n");
}

function compiledStylesheetDefines(string $css, string $class): bool
{
    $escaped = preg_quote((string) preg_replace('/([^a-zA-Z0-9_-])/', '\\\\$1', $class), '/');

    // A trailing backslash would mean we matched `.text-blood` inside
    // `.text-blood\/10`, which is a different utility.
    return preg_match('/\.'.$escaped.'(?![a-zA-Z0-9_\\\\-])/', $css) === 1;
}

it('exposes every design colour variable to Tailwind utilities', function () {
    $stylesheet = stylesheetSource();

    preg_match_all('/--(?<name>[a-z0-9-]+)\s*:\s*(?<value>[^;]+);/', cssBlock($stylesheet, ':root'), $rootMatches, PREG_SET_ORDER);
    preg_match_all('/--color-(?<name>[a-z0-9-]+)\s*:\s*var\(--(?<source>[a-z0-9-]+)\);/', themeBlock($stylesheet), $themeMatches, PREG_SET_ORDER);

    $designColours = collect($rootMatches)
        ->filter(fn (array $match): bool => preg_match('/^\s*(#|rgba?\(|hsla?\()/i', $match['value']) === 1)
        ->map(fn (array $match): string => $match['name'])
        ->sort()
        ->values();

    $utilityColours = collect($themeMatches)
        ->mapWithKeys(fn (array $match): array => [$match['name'] => $match['source']]);

    expect($designColours)->not->toBeEmpty();

    foreach ($designColours as $colour) {
        expect($utilityColours->get($colour))
            ->toBe($colour, "Design colour [--{$colour}] must be exposed as [--color-{$colour}].");
    }
});

it('draws every colour utility the components use from a rule that really exists', function () {
    $css = compiledStylesheet();

    $used = collect([
        ...File::allFiles(resource_path('js/Components')),
        ...File::allFiles(resource_path('js/Layouts')),
        ...File::allFiles(resource_path('js/Pages')),
    ])
        ->flatMap(function (SplFileInfo $file) {
            $source = File::get($file->getPathname());
            preg_match_all(
                '/className\s*=\s*(?:"(?<double>[^"]*)"|\'(?<single>[^\']*)\'|\{`(?<template>.*?)`\})/s',
                $source,
                $classNameMatches,
                PREG_SET_ORDER,
            );
            $classNames = collect($classNameMatches)
                ->map(fn (array $match): string => $match['double'] ?: ($match['single'] ?: ($match['template'] ?? '')))
                ->implode(' ');

            // Whatever a template literal computes at runtime, Tailwind never
            // saw it either, so it is not something this test can answer.
            $classNames = preg_replace('/\$\{[^}]*\}/', ' ', $classNames) ?? $classNames;
            $classNames = preg_replace('/\[[^\]]+\]/', ' ', $classNames) ?? $classNames;

            preg_match_all(
                '/(?<![A-Za-z0-9_-])(?<utility>(?:[a-z-]+:)*(?:text|bg|border|outline|ring|decoration|divide|accent|caret|fill|stroke)-[a-z][a-z0-9-]*(?:\/\d+)?)(?![A-Za-z0-9_-])/',
                $classNames,
                $matches,
                PREG_SET_ORDER,
            );

            return collect($matches)->map(fn (array $match): array => [
                'file' => $file->getRelativePathname(),
                'utility' => $match['utility'],
            ]);
        })
        ->unique(fn (array $match): string => $match['utility'])
        ->values();

    expect($used)->not->toBeEmpty();

    // A colour the theme does not expose compiles to nothing at all, and the
    // element quietly inherits whatever its parent had.
    $missing = $used
        ->reject(fn (array $match): bool => compiledStylesheetDefines($css, $match['utility']))
        ->map(fn (array $match): string => "{$match['utility']} ({$match['file']})")
        ->all();

    expect($missing)->toBe([], 'These utilities produce no rule in the compiled stylesheet. If one was just added, run: npm run build');
});

it('does not ship the deleted readable fallback stylesheet', function () {
    $stylesheet = stylesheetSource();

    expect($stylesheet)->not->toContain('readable-fallback');
});

it('does not allow arbitrary mid-word text breaking in visitor components', function () {
    $forbidden = [
        'break-words',
        'break-all',
        '[overflow-wrap:anywhere]',
        '[overflow-wrap:break-word]',
        '[word-break:break-all]',
        '[word-break:break-word]',
        'hyphens-auto',
        'overflow-wrap: anywhere',
        'overflow-wrap:anywhere',
        'overflow-wrap: break-word',
        'overflow-wrap:break-word',
        'word-break: break-all',
        'word-break:break-all',
        'word-break: break-word',
        'word-break:break-word',
        'hyphens: auto',
        'hyphens:auto',
    ];

    collect([
        ...File::allFiles(resource_path('js/Components')),
        ...File::allFiles(resource_path('js/Layouts')),
        ...File::allFiles(resource_path('js/Pages')),
    ])->each(function (SplFileInfo $file) use ($forbidden) {
        $source = File::get($file->getPathname());

        foreach ($forbidden as $needle) {
            expect($source)->not->toContain(
                $needle,
                "Visitor component [{$file->getRelativePathname()}] must not use arbitrary mid-word text breaking [{$needle}].",
            );
        }
    });

    $stylesheet = stylesheetSource();

    foreach ($forbidden as $needle) {
        expect($stylesheet)->not->toContain(
            $needle,
            "Stylesheet must not use arbitrary mid-word text breaking [{$needle}].",
        );
    }
});
