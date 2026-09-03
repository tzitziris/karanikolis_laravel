<?php

namespace App\Services;

use finfo;
use GdImage;
use RuntimeException;

class StaticImageService
{
    /**
     * @return array{name: string, source_bytes: int, original_width: int, original_height: int, derivatives: array<int, array{path: string, width: int, height: int, bytes: int, status: string}>}
     */
    public function convert(string $name, string $sourcePath, bool $force = false): array
    {
        $this->validateName($name);
        $this->ensureOutputDirectory();

        $info = $this->inspect($sourcePath);
        $source = null;
        $written = [];

        try {
            $source = $this->createSource($sourcePath, $info['mime']);

            foreach ($this->targetWidths((int) $info['width']) as $targetWidth) {
                $targetHeight = $this->proportionalHeight(
                    (int) $info['width'],
                    (int) $info['height'],
                    $targetWidth,
                );
                $destination = $this->publicPathFor($name, $targetWidth);

                $status = 'skipped';

                if ($force || ! $this->validDerivativeExists($destination, $targetWidth, $targetHeight)) {
                    $this->writeWebpDerivative($source, $targetWidth, $targetHeight, $destination);
                    $status = 'written';
                }

                $written[] = [
                    'bytes' => (int) filesize($destination),
                    'height' => $targetHeight,
                    'path' => $destination,
                    'status' => $status,
                    'width' => $targetWidth,
                ];
            }
        } catch (RuntimeException $exception) {
            foreach ($written as $derivative) {
                if (($derivative['status'] ?? null) === 'written' && is_file($derivative['path'])) {
                    @unlink($derivative['path']);
                }
            }

            throw $exception;
        } finally {
            $this->destroyGdImage($source);
        }

        return [
            'derivatives' => $written,
            'name' => $name,
            'original_height' => (int) $info['height'],
            'original_width' => (int) $info['width'],
            'source_bytes' => (int) $info['bytes'],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function configuredWidths(): array
    {
        $widths = config('images.static.widths', []);

        if (! is_array($widths)) {
            throw new RuntimeException('Static image widths must be configured as an array.');
        }

        $widths = array_values(array_unique(array_map('intval', $widths)));
        sort($widths);

        foreach ($widths as $width) {
            if ($width < 1 || $width > $this->limit('max_dimension')) {
                throw new RuntimeException('Static image widths contain an invalid value.');
            }
        }

        if ($widths === []) {
            throw new RuntimeException('At least one static image width must be configured.');
        }

        return $widths;
    }

    /**
     * @return array<int, int>
     */
    public function targetWidths(int $sourceWidth): array
    {
        return array_values(array_filter(
            $this->configuredWidths(),
            fn (int $width): bool => $width <= $sourceWidth,
        ));
    }

    public function relativePathFor(string $name, int $width): string
    {
        $this->validateName($name);

        if (! in_array($width, $this->configuredWidths(), true)) {
            throw new RuntimeException('Static image width is not configured.');
        }

        return trim($this->outputDir(), '/')."/{$name}-{$width}.webp";
    }

    /**
     * @return array{bytes: int, height: int, mime: string, width: int}
     */
    public function inspect(string $sourcePath): array
    {
        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw new RuntimeException("Source image is not readable: {$sourcePath}");
        }

        $bytes = filesize($sourcePath);

        if (! is_int($bytes) || $bytes < 1 || $bytes > $this->limit('max_bytes')) {
            throw new RuntimeException('Source image byte size is outside configured limits.');
        }

        $imageInfo = @getimagesize($sourcePath);

        if (! is_array($imageInfo) || ! isset($imageInfo[0], $imageInfo[1])) {
            throw new RuntimeException('Source file is not a valid image.');
        }

        $width = (int) $imageInfo[0];
        $height = (int) $imageInfo[1];
        $minDimension = $this->limit('min_dimension');
        $maxDimension = $this->limit('max_dimension');
        $maxPixels = $this->limit('max_pixels');

        if (
            $width < $minDimension
            || $height < $minDimension
            || $width > $maxDimension
            || $height > $maxDimension
            || $width * $height > $maxPixels
        ) {
            throw new RuntimeException('Source image dimensions are outside configured limits.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($sourcePath);
        $mime = is_string($mime) ? $mime : '';

        if (! in_array($mime, $this->allowedMimes(), true)) {
            throw new RuntimeException("Unsupported source image MIME type: {$mime}");
        }

        return [
            'bytes' => $bytes,
            'height' => $height,
            'mime' => $mime,
            'width' => $width,
        ];
    }

    protected function writeWebpDerivative(GdImage $source, int $targetWidth, int $targetHeight, string $destination): void
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('PHP GD WebP support is not available.');
        }

        $output = null;
        $temporary = $destination.'.tmp-'.getmypid();

        try {
            $output = imagecreatetruecolor($targetWidth, $targetHeight);

            if (! $output instanceof GdImage) {
                throw new RuntimeException('Unable to allocate output image.');
            }

            imagealphablending($output, false);
            imagesavealpha($output, true);
            $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
            imagefill($output, 0, 0, $transparent);

            $resampled = imagecopyresampled(
                $output,
                $source,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                imagesx($source),
                imagesy($source),
            );

            if (! $resampled) {
                throw new RuntimeException('Unable to resize source image.');
            }

            if (! @imagewebp($output, $temporary, (int) config('images.static.quality'))) {
                throw new RuntimeException('Unable to encode WebP derivative.');
            }

            clearstatcache(true, $temporary);

            if (! is_file($temporary) || (int) filesize($temporary) < 1) {
                throw new RuntimeException('WebP derivative was not written.');
            }

            if (is_file($destination) && hash_file('sha256', $destination) === hash_file('sha256', $temporary)) {
                @unlink($temporary);

                return;
            }

            if (! @rename($temporary, $destination)) {
                throw new RuntimeException('Unable to move WebP derivative into place.');
            }
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }

            $this->destroyGdImage($output);
        }
    }

    protected function publicPathFor(string $name, int $width): string
    {
        return public_path($this->relativePathFor($name, $width));
    }

    private function createSource(string $sourcePath, string $mime): GdImage
    {
        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            default => null,
        };

        if (! $source instanceof GdImage) {
            throw new RuntimeException('Unable to decode source image with GD.');
        }

        return $source;
    }

    private function validDerivativeExists(string $path, int $width, int $height): bool
    {
        if (! is_file($path) || (int) filesize($path) < 1) {
            return false;
        }

        $info = @getimagesize($path);

        return is_array($info)
            && (int) ($info[0] ?? 0) === $width
            && (int) ($info[1] ?? 0) === $height
            && (($info['mime'] ?? null) === 'image/webp');
    }

    private function proportionalHeight(int $sourceWidth, int $sourceHeight, int $targetWidth): int
    {
        if ($sourceWidth < 1 || $sourceHeight < 1 || $targetWidth < 1) {
            throw new RuntimeException('Invalid image dimensions.');
        }

        return max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
    }

    private function ensureOutputDirectory(): void
    {
        $directory = public_path($this->outputDir());

        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create static image output directory.');
        }

        if (! is_writable($directory)) {
            throw new RuntimeException('Static image output directory is not writable.');
        }
    }

    private function outputDir(): string
    {
        $directory = config('images.static.output_dir');

        if (! is_string($directory) || trim($directory) === '' || str_contains($directory, '..')) {
            throw new RuntimeException('Static image output directory is invalid.');
        }

        return trim($directory, '/');
    }

    /**
     * @return array<int, string>
     */
    private function allowedMimes(): array
    {
        $mimes = config('images.static.limits.allowed_mimes', []);

        if (! is_array($mimes) || $mimes === []) {
            throw new RuntimeException('Static image MIME allow-list is invalid.');
        }

        return array_values($mimes);
    }

    private function limit(string $key): int
    {
        $value = config("images.static.limits.{$key}");

        if (! is_numeric($value) || (int) $value < 1) {
            throw new RuntimeException("Static image limit [{$key}] is invalid.");
        }

        return (int) $value;
    }

    private function validateName(string $name): void
    {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $name) !== 1) {
            throw new RuntimeException("Invalid static image name: {$name}");
        }
    }

    private function destroyGdImage(mixed $image): void
    {
        if ($image instanceof GdImage) {
            imagedestroy($image);
        }
    }
}
