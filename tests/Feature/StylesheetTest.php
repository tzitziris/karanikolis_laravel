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

it('only uses project colour utilities that are exposed by the theme', function () {
    $stylesheet = stylesheetSource();
    preg_match_all('/--color-(?<name>[a-z0-9-]+)\s*:/', themeBlock($stylesheet), $themeMatches);

    $themeColours = collect($themeMatches['name'])->unique();
    $knownNonColourTokens = collect([
        'b',
        'base',
        'black',
        'current',
        'e',
        'lg',
        'none',
        'offset-2',
        'r',
        's',
        'sm',
        't',
        'transparent',
        'white',
        'xl',
        'xs',
    ]);

    $usedColourUtilities = collect([
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
            $classNames = preg_replace('/\[[^\]]+\]/', ' ', $classNames) ?? $classNames;

            preg_match_all(
                '/(?<![A-Za-z0-9_-])(?<utility>(?:[a-z-]+:)*(?:text|bg|border|outline|ring|decoration|divide|accent|caret|fill|stroke)-(?<token>[a-z][a-z0-9-]*)(?:\/\d+)?)(?![A-Za-z0-9_-])/',
                $classNames,
                $matches,
                PREG_SET_ORDER,
            );

            return collect($matches)->map(fn (array $match): array => [
                'file' => $file->getRelativePathname(),
                'token' => $match['token'],
                'utility' => $match['utility'],
            ]);
        })
        ->reject(fn (array $match): bool => $knownNonColourTokens->contains($match['token']))
        ->unique(fn (array $match): string => $match['utility'])
        ->values();

    expect($usedColourUtilities)->not->toBeEmpty();

    foreach ($usedColourUtilities as $match) {
        expect($themeColours->contains($match['token']))
            ->toBeTrue("Colour utility [{$match['utility']}] in [{$match['file']}] is not exposed by @theme.");
    }
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
