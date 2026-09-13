<?php

use Illuminate\Support\Facades\File;

/**
 * @return array<string, array{unicodeRange: string, sha256: string, bytes: int}>
 */
function fontCoverageRecord(): array
{
    return json_decode(File::get(resource_path('css/font-coverage.generated.json')), true);
}

/**
 * @return array<string, string>
 */
function declaredFontRanges(): array
{
    preg_match_all(
        '/src:\s*url\("\/fonts\/[^"]*\/(?<stem>[^\/"]+)\.woff2"\)\s*format\("woff2"\);\s*unicode-range:\s*(?<range>[^;]+);/',
        File::get(resource_path('css/app.css')),
        $matches,
        PREG_SET_ORDER,
    );

    return collect($matches)
        ->mapWithKeys(fn (array $m): array => [$m['stem'] => trim($m['range'])])
        ->all();
}

it('ships exactly the font files the coverage record describes', function () {
    $record = fontCoverageRecord();
    $shipped = collect(File::glob(public_path('fonts/*/*.woff2')))
        ->mapWithKeys(fn (string $path): array => [basename($path, '.woff2') => $path]);

    expect($shipped->keys()->sort()->values()->all())
        ->toBe(collect($record)->keys()->sort()->values()->all());

    foreach ($shipped as $stem => $path) {
        // A font swapped without rerunning scripts/subset-fonts.py would leave the
        // stylesheet promising coverage the file no longer has, and the browser
        // would draw empty boxes rather than fall back.
        expect(hash_file('sha256', $path))->toBe(
            $record[$stem]['sha256'],
            "{$stem}.woff2 does not match the coverage record. Rerun scripts/subset-fonts.py.",
        );
        expect(filesize($path))->toBe($record[$stem]['bytes']);
    }
});

it('promises in the stylesheet only the characters the font files actually contain', function () {
    $record = fontCoverageRecord();
    $declared = declaredFontRanges();

    expect(array_keys($declared))->not->toBeEmpty();

    foreach ($declared as $stem => $range) {
        expect($record)->toHaveKey($stem);
        expect($range)->toBe(
            $record[$stem]['unicodeRange'],
            "The unicode-range for {$stem} does not describe what is in the file.",
        );
    }

    expect(count($declared))->toBe(count($record));
});

it('does not send a Greek face to draw a Latin character, or the reverse', function () {
    foreach (declaredFontRanges() as $stem => $range) {
        $codepoints = collect(explode(',', $range))
            ->map(fn (string $part): string => trim(str_replace('U+', '', $part)))
            ->flatMap(function (string $part): array {
                if (! str_contains($part, '-')) {
                    return [hexdec($part)];
                }
                [$from, $to] = explode('-', $part);

                return range(hexdec($from), hexdec($to));
            });

        $greekBlock = fn (int $cp): bool => $cp >= 0x0370 && $cp <= 0x03FF;

        if (str_contains($stem, 'greek')) {
            expect($codepoints->reject($greekBlock)->all())->toBe(
                [],
                "{$stem} is declared for characters outside the Greek block, so a browser would fetch it to draw them.",
            );
        } else {
            expect($codepoints->filter($greekBlock)->all())->toBe([], "{$stem} is declared for Greek characters.");
        }
    }
});

it('keeps the critical path under the weight that made the old site feel frozen', function () {
    $total = collect(File::glob(public_path('fonts/*/*.woff2')))
        ->sum(fn (string $path): int => filesize($path));

    // 166 KB of fonts were preloaded before these were subset; the point of the
    // rebuild is that the first paint is not waiting on them.
    expect($total)->toBeLessThan(110 * 1024);
});
