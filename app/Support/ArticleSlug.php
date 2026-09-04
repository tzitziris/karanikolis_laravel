<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Support\Str;
use Normalizer;

class ArticleSlug
{
    /**
     * @var array<string, string>
     */
    private const GREEK_TO_LATIN = [
        'α' => 'a',
        'β' => 'v',
        'γ' => 'g',
        'δ' => 'd',
        'ε' => 'e',
        'ζ' => 'z',
        'η' => 'i',
        'θ' => 'th',
        'ι' => 'i',
        'κ' => 'k',
        'λ' => 'l',
        'μ' => 'm',
        'ν' => 'n',
        'ξ' => 'x',
        'ο' => 'o',
        'π' => 'p',
        'ρ' => 'r',
        'σ' => 's',
        'ς' => 's',
        'τ' => 't',
        'υ' => 'y',
        'φ' => 'f',
        'χ' => 'ch',
        'ψ' => 'ps',
        'ω' => 'o',
    ];

    /**
     * @var array<string, string>
     */
    private const VOWEL_DIGRAPHS = [
        'αυ' => 'a',
        'ευ' => 'e',
        'ηυ' => 'i',
    ];

    /**
     * @var array<int, string>
     */
    private const VOICELESS_AFTER_UPSILON = ['θ', 'κ', 'ξ', 'π', 'σ', 'ς', 'τ', 'φ', 'χ', 'ψ'];

    public static function fromTitle(string $title): string
    {
        $normalized = trim(mb_strtolower($title));

        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($normalized, Normalizer::FORM_D) ?: $normalized;
        }

        $normalized = preg_replace('/\p{Mn}+/u', '', $normalized) ?: $normalized;
        $characters = preg_split('//u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $slug = '';
        $wordStart = true;

        for ($index = 0; $index < count($characters); $index++) {
            $character = $characters[$index];
            $next = $characters[$index + 1] ?? null;
            $pair = is_string($next) ? $character.$next : null;

            if ($pair !== null && self::isGreekLetter($character) && self::isGreekLetter($next)) {
                $digraph = self::transliterateDigraph($pair, $wordStart, $characters[$index + 2] ?? null);

                if ($digraph !== null) {
                    $slug .= $digraph;
                    $index++;
                    $wordStart = false;

                    continue;
                }
            }

            if (isset(self::GREEK_TO_LATIN[$character])) {
                $slug .= self::GREEK_TO_LATIN[$character];
                $wordStart = false;

                continue;
            }

            if (preg_match('/[a-z0-9]/', $character) === 1) {
                $slug .= $character;
                $wordStart = false;

                continue;
            }

            if ($slug !== '' && ! str_ends_with($slug, '-')) {
                $slug .= '-';
            }

            $wordStart = true;
        }

        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'article-'.self::stableHash($title);
    }

    public static function uniqueForTitle(string $title): string
    {
        $base = Str::limit(self::fromTitle($title), 180, '');

        for ($suffix = 1; $suffix <= 100; $suffix++) {
            $candidate = $suffix === 1 ? $base : "{$base}-{$suffix}";

            if (! Article::query()->where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        return Str::limit($base, 181, '').'-'.self::stableHash($title.now()->toJSON());
    }

    private static function stableHash(string $value): string
    {
        return strtolower(base_convert(sprintf('%u', crc32($value)), 10, 36));
    }

    private static function transliterateDigraph(string $pair, bool $wordStart, ?string $after): ?string
    {
        if ($pair === 'ου') {
            return 'ou';
        }

        if (isset(self::VOWEL_DIGRAPHS[$pair])) {
            return self::VOWEL_DIGRAPHS[$pair].self::upsilonSoundBefore($after);
        }

        return match ($pair) {
            'μπ' => $wordStart ? 'b' : 'mp',
            'ντ' => $wordStart ? 'd' : 'nt',
            'γκ' => $wordStart ? 'g' : 'gk',
            'τσ' => 'ts',
            'τζ' => 'tz',
            default => null,
        };
    }

    private static function upsilonSoundBefore(?string $after): string
    {
        return in_array($after, self::VOICELESS_AFTER_UPSILON, true) ? 'f' : 'v';
    }

    private static function isGreekLetter(string $character): bool
    {
        return preg_match('/^[α-ω]$/u', $character) === 1;
    }
}
