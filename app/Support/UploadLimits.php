<?php

namespace App\Support;

class UploadLimits
{
    public static function articleImageMaxBytes(): int
    {
        return min(
            (int) config('images.uploads.limits.max_bytes'),
            self::iniBytes('upload_max_filesize'),
            self::iniBytes('post_max_size'),
        );
    }

    public static function articleImageMaxKilobytes(): int
    {
        return (int) floor(self::articleImageMaxBytes() / 1024);
    }

    public static function articleImageMaxLabel(): string
    {
        $bytes = self::articleImageMaxBytes();

        // A host with a tiny limit must not be described as "0,0 MB", and a
        // limit below a kilobyte must not be rounded up into a promise.
        if ($bytes < 1024) {
            return $bytes.' bytes';
        }

        if ($bytes < 1048576) {
            return (int) floor($bytes / 1024).' KB';
        }

        $megabytes = (float) ($bytes / 1048576);

        // In PHP an exact division returns an int, so floor() and the value
        // compare unequal under === even when they hold the same number.
        if (floor($megabytes) == $megabytes) {
            return (int) $megabytes.' MB';
        }

        return number_format($megabytes, 1, ',', '').' MB';
    }

    public static function iniBytes(string $key): int
    {
        $value = trim((string) ini_get($key));

        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
