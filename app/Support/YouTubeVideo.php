<?php

namespace App\Support;

class YouTubeVideo
{
    public static function idFromUrl(string $url): ?string
    {
        $parts = parse_url(trim($url));

        if (! is_array($parts) || ! isset($parts['host'])) {
            return null;
        }

        $host = preg_replace('/^(www\.|m\.)/', '', strtolower($parts['host']));
        $path = trim($parts['path'] ?? '', '/');
        $query = [];

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $id = match ($host) {
            'youtu.be' => explode('/', $path)[0] ?? null,
            'youtube.com', 'youtube-nocookie.com' => self::idFromYouTubePath($path, $query),
            default => null,
        };

        return is_string($id) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) === 1 ? $id : null;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private static function idFromYouTubePath(string $path, array $query): ?string
    {
        $parts = array_values(array_filter(explode('/', $path)));
        $first = $parts[0] ?? '';

        if ($first === 'watch') {
            return is_string($query['v'] ?? null) ? $query['v'] : null;
        }

        if (in_array($first, ['embed', 'shorts', 'live', 'v'], true)) {
            return $parts[1] ?? null;
        }

        return is_string($query['v'] ?? null) ? $query['v'] : null;
    }
}
