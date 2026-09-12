<?php

namespace App\Services;

use App\Exceptions\ArticleImageUploadException;
use App\Models\Article;
use App\Models\ArticleImage;
use finfo;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class UploadedArticleImageService
{
    public const NAME_PREFIX = 'uploads/articles/';

    /**
     * @return array{name: string, width: int, height: int, source_bytes: int, derivatives: array<int, array{path: string, width: int, height: int, bytes: int}>}
     */
    public function store(UploadedFile $file, string $role): array
    {
        $this->validateRole($role);
        $this->ensureOutputDirectory();

        $sourcePath = $file->getRealPath() ?: $file->getPathname();
        $info = $this->inspect($file, $sourcePath);
        $source = null;
        $oriented = null;
        $written = [];
        $name = self::NAME_PREFIX.(string) Str::uuid();

        try {
            $source = $this->createSource($sourcePath, $info['mime']);
            $oriented = $this->applyOrientation($source, $sourcePath, $info['mime']);

            if ($oriented !== $source) {
                $this->destroyGdImage($source);
                $source = null;
            }

            $width = imagesx($oriented);
            $height = imagesy($oriented);

            foreach ($this->targetWidths($width, $role) as $targetWidth) {
                $targetHeight = $this->proportionalHeight($width, $height, $targetWidth);
                $destination = public_path("images/{$name}-{$targetWidth}.webp");

                $this->writeWebpDerivative($oriented, $targetWidth, $targetHeight, $destination);
                $written[] = [
                    'bytes' => (int) filesize($destination),
                    'height' => $targetHeight,
                    'path' => $destination,
                    'width' => $targetWidth,
                ];
            }

            return [
                'derivatives' => $written,
                'height' => $height,
                'name' => $name,
                'source_bytes' => $info['bytes'],
                'width' => $width,
            ];
        } catch (ArticleImageUploadException $exception) {
            $this->deletePaths(array_column($written, 'path'));

            throw $exception;
        } catch (RuntimeException $exception) {
            $this->deletePaths(array_column($written, 'path'));

            throw ArticleImageUploadException::ownerMessage('Η φωτογραφία δεν μπόρεσε να μετατραπεί. Δοκιμάστε άλλη φωτογραφία.');
        } finally {
            $this->destroyGdImage($source);
            $this->destroyGdImage($oriented);
        }
    }

    public function isUploadedName(?string $name): bool
    {
        return is_string($name) && str_starts_with($name, self::NAME_PREFIX);
    }

    /**
     * @return array<int, int>
     */
    public function widthsFor(string $role, int $sourceWidth): array
    {
        $this->validateRole($role);

        return $this->targetWidths($sourceWidth, $role);
    }

    /**
     * @param  array<int, string|null>  $names
     */
    public function deleteUnreferenced(array $names): void
    {
        foreach (array_values(array_unique(array_filter($names))) as $name) {
            if (! $this->isUploadedName($name) || $this->hasDatabaseReference($name)) {
                continue;
            }

            $this->deleteDerivatives($name);
        }
    }

    public function deleteDerivatives(string $name): void
    {
        if (! $this->isUploadedName($name)) {
            return;
        }

        $this->deletePaths(File::glob(public_path("images/{$name}-*.webp")) ?: []);
    }

    /**
     * @return array{bytes: int, height: int, mime: string, width: int}
     */
    private function inspect(UploadedFile $file, string $sourcePath): array
    {
        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw ArticleImageUploadException::ownerMessage('Η φωτογραφία δεν διαβάζεται. Ανεβάστε ξανά το αρχείο.');
        }

        $bytes = filesize($sourcePath);

        if (! is_int($bytes) || $bytes < 1 || $bytes > $this->limit('max_bytes')) {
            throw ArticleImageUploadException::ownerMessage('Η φωτογραφία είναι πολύ μεγάλη. Ανεβάστε μικρότερο αρχείο.');
        }

        $declaredMime = (string) $file->getClientMimeType();
        $actualMime = (new finfo(FILEINFO_MIME_TYPE))->file($sourcePath);
        $actualMime = is_string($actualMime) ? $actualMime : '';
        $allowedMimes = $this->allowedMimes();

        if (! in_array($declaredMime, $allowedMimes, true) || ! in_array($actualMime, $allowedMimes, true)) {
            throw ArticleImageUploadException::ownerMessage('Το αρχείο δεν είναι αποδεκτή φωτογραφία. Χρησιμοποιήστε JPEG ή PNG.');
        }

        if ($declaredMime !== $actualMime) {
            throw ArticleImageUploadException::ownerMessage('Ο τύπος του αρχείου δεν ταιριάζει με το περιεχόμενό του. Ανεβάστε την αρχική φωτογραφία ως JPEG ή PNG.');
        }

        $imageInfo = @getimagesize($sourcePath);

        if (! is_array($imageInfo) || ! isset($imageInfo[0], $imageInfo[1])) {
            throw ArticleImageUploadException::ownerMessage('Το αρχείο δεν είναι έγκυρη φωτογραφία.');
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
            throw ArticleImageUploadException::ownerMessage('Οι διαστάσεις της φωτογραφίας είναι εκτός ορίων. Ανεβάστε μικρότερη φωτογραφία.');
        }

        return [
            'bytes' => $bytes,
            'height' => $height,
            'mime' => $actualMime,
            'width' => $width,
        ];
    }

    private function createSource(string $sourcePath, string $mime): GdImage
    {
        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            default => null,
        };

        if (! $source instanceof GdImage) {
            throw ArticleImageUploadException::ownerMessage('Η φωτογραφία δεν μπόρεσε να ανοιχτεί. Δοκιμάστε άλλη φωτογραφία.');
        }

        return $source;
    }

    private function applyOrientation(GdImage $source, string $sourcePath, string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $source;
        }

        $exif = @exif_read_data($sourcePath);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        return match ($orientation) {
            3 => imagerotate($source, 180, 0) ?: $source,
            6 => imagerotate($source, -90, 0) ?: $source,
            8 => imagerotate($source, 90, 0) ?: $source,
            default => $source,
        };
    }

    private function writeWebpDerivative(GdImage $source, int $targetWidth, int $targetHeight, string $destination): void
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

            if (! imagecopyresampled($output, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($source), imagesy($source))) {
                throw new RuntimeException('Unable to resize source image.');
            }

            if (! @imagewebp($output, $temporary, (int) config('images.uploads.quality'))) {
                throw new RuntimeException('Unable to encode WebP derivative.');
            }

            clearstatcache(true, $temporary);

            if (! is_file($temporary) || (int) filesize($temporary) < 1) {
                throw new RuntimeException('WebP derivative was not written.');
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

    /**
     * @return array<int, int>
     */
    private function targetWidths(int $sourceWidth, string $role): array
    {
        $widths = array_values(array_filter(
            $this->configuredWidths($role),
            fn (int $width): bool => $width <= $sourceWidth,
        ));

        return $widths === [] ? [$sourceWidth] : $widths;
    }

    /**
     * @return array<int, int>
     */
    private function configuredWidths(string $role): array
    {
        $widths = config("images.uploads.roles.{$role}.widths", []);

        if (! is_array($widths)) {
            throw new RuntimeException('Uploaded image widths must be configured as an array.');
        }

        $widths = array_values(array_unique(array_map('intval', $widths)));
        sort($widths);

        foreach ($widths as $width) {
            if ($width < 1 || $width > $this->limit('max_dimension')) {
                throw new RuntimeException('Uploaded image widths contain an invalid value.');
            }
        }

        if ($widths === []) {
            throw new RuntimeException('At least one uploaded image width must be configured.');
        }

        return $widths;
    }

    private function proportionalHeight(int $sourceWidth, int $sourceHeight, int $targetWidth): int
    {
        return max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
    }

    private function ensureOutputDirectory(): void
    {
        $directory = public_path((string) config('images.uploads.article_dir'));

        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create uploaded image output directory.');
        }

        if (! is_writable($directory)) {
            throw new RuntimeException('Uploaded image output directory is not writable.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function allowedMimes(): array
    {
        $mimes = config('images.uploads.limits.allowed_mimes', []);

        if (! is_array($mimes) || $mimes === []) {
            throw new RuntimeException('Uploaded image MIME allow-list is invalid.');
        }

        return array_values($mimes);
    }

    private function limit(string $key): int
    {
        $value = config("images.uploads.limits.{$key}");

        if (! is_numeric($value) || (int) $value < 1) {
            throw new RuntimeException("Uploaded image limit [{$key}] is invalid.");
        }

        return (int) $value;
    }

    private function validateRole(string $role): void
    {
        if (! in_array($role, ['cover', 'gallery'], true)) {
            throw new RuntimeException("Invalid uploaded image role [{$role}].");
        }
    }

    private function hasDatabaseReference(string $name): bool
    {
        return Article::query()->where('cover_image_name', $name)->exists()
            || ArticleImage::query()->where('image_name', $name)->exists();
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deletePaths(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function destroyGdImage(mixed $image): void
    {
        if ($image instanceof GdImage) {
            imagedestroy($image);
        }
    }
}
