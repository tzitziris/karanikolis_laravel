<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * The picture a link preview shows when the school posts a page on Facebook.
 *
 * A social crawler does not run JavaScript, so it never reaches the components
 * that build image URLs on the page. This is the server's way to the same
 * files: the widths come from the same manifest and the same upload service the
 * components read, and every address is checked against the disk before it is
 * offered, so nothing here can name a file the pipeline did not write.
 */
class ShareImage
{
    /**
     * Facebook draws a large card from roughly 1200px wide and falls back to a
     * thumbnail below 600. Take the narrowest derivative that clears 1200 so
     * the preview is sharp without handing a crawler the 2400px file.
     */
    private const PREFERRED_WIDTH = 1200;

    private const DEFAULT_STATIC_IMAGE = 'hero-kickboxing';

    public function __construct(private readonly UploadedArticleImageService $uploadedImages) {}

    /**
     * An article cover is either a photograph the owner uploaded or one of the
     * pictures that ship with the site. Both are webp derivatives written by the
     * pipeline; neither is ever the original file.
     *
     * @return array{url: string, width: int, height: int}|null
     */
    public function forCover(?string $name, ?int $width, ?int $height): ?array
    {
        if (! filled($name)) {
            return null;
        }

        if (! $this->uploadedImages->isUploadedName($name)) {
            return $this->forStaticImage($name);
        }

        if (! is_int($width) || ! is_int($height) || $width < 1 || $height < 1) {
            return null;
        }

        $chosen = $this->chooseWidth($this->uploadedImages->widthsFor('cover', $width));

        if ($chosen === null) {
            return null;
        }

        return $this->describe("/images/{$name}-{$chosen}.webp", $chosen, $width, $height);
    }

    /**
     * @return array{url: string, width: int, height: int}|null
     */
    public function fallback(): ?array
    {
        return $this->forStaticImage(self::DEFAULT_STATIC_IMAGE);
    }

    /**
     * @return array{url: string, width: int, height: int}|null
     */
    private function forStaticImage(string $name): ?array
    {
        $manifest = $this->staticManifest();
        $image = $manifest['images'][$name] ?? null;

        if (! is_array($image)) {
            return null;
        }

        $chosen = $this->chooseWidth($image['widths'] ?? []);

        if ($chosen === null) {
            return null;
        }

        $base = rtrim((string) ($manifest['basePath'] ?? ''), '/');

        return $this->describe(
            $base.'/'.$name."-{$chosen}.webp",
            $chosen,
            (int) $image['width'],
            (int) $image['height'],
        );
    }

    /**
     * @param  array<int, int>  $widths
     */
    private function chooseWidth(array $widths): ?int
    {
        $widths = array_values(array_filter($widths, fn (mixed $w): bool => is_int($w) && $w > 0));

        if ($widths === []) {
            return null;
        }

        sort($widths);

        foreach ($widths as $width) {
            if ($width >= self::PREFERRED_WIDTH) {
                return $width;
            }
        }

        return end($widths);
    }

    /**
     * @return array{url: string, width: int, height: int}|null
     */
    private function describe(string $url, int $renderedWidth, int $sourceWidth, int $sourceHeight): ?array
    {
        // A preview that points at a file which is not there shows nothing at
        // all, and the owner would have no way to tell from the page itself.
        if ($sourceWidth < 1 || ! File::exists(public_path(ltrim($url, '/')))) {
            return null;
        }

        return [
            'height' => (int) round($sourceHeight * ($renderedWidth / $sourceWidth)),
            // A crawler is on another machine, so a path starting with a slash
            // means nothing to it. Only an absolute address is usable.
            'url' => url($url),
            'width' => $renderedWidth,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function staticManifest(): array
    {
        $path = (string) config('images.static.manifest_path');

        if (! File::exists($path)) {
            return [];
        }

        return json_decode(File::get($path), true) ?: [];
    }
}
