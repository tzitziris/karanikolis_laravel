<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class ArticleBodyRenderer
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $imageManifest = null;

    /**
     * @param  array<string, mixed>|null  $body
     */
    public function render(?array $body): string
    {
        if (! is_array($body)) {
            return '<p>Το περιεχόμενο δεν είναι διαθέσιμο.</p>';
        }

        return $this->renderNode($body);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderNode(array $node): string
    {
        $type = $this->stringValue($node['type'] ?? null);

        return match ($type) {
            'doc' => $this->renderChildren($node),
            'text' => $this->renderText($node),
            'paragraph' => $this->wrap('p', $this->renderChildren($node), $this->alignmentAttribute($node)),
            'heading' => $this->renderHeading($node),
            'bulletList' => $this->wrap('ul', $this->renderChildren($node)),
            'orderedList' => $this->wrap('ol', $this->renderChildren($node)),
            'listItem' => $this->wrap('li', $this->renderChildren($node)),
            'blockquote' => $this->wrap('blockquote', $this->renderChildren($node)),
            'hardBreak' => '<br>',
            'image' => $this->renderImage($node),
            'youtube' => '',
            default => $this->renderChildren($node),
        };
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderText(array $node): string
    {
        $html = e($this->stringValue($node['text'] ?? null));
        $marks = is_array($node['marks'] ?? null) ? $node['marks'] : [];

        foreach ($marks as $mark) {
            if (! is_array($mark)) {
                continue;
            }

            $html = match ($this->stringValue($mark['type'] ?? null)) {
                'bold' => "<strong>{$html}</strong>",
                'italic' => "<em>{$html}</em>",
                'link' => $this->renderLinkMark($html, $mark),
                default => $html,
            };
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $mark
     */
    private function renderLinkMark(string $html, array $mark): string
    {
        $attrs = is_array($mark['attrs'] ?? null) ? $mark['attrs'] : [];
        $href = $this->safeWebUrl($this->stringValue($attrs['href'] ?? null));

        if ($href === null) {
            return $html;
        }

        return '<a href="'.$this->attribute($href).'">'.$html.'</a>';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderHeading(array $node): string
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $level = (int) ($attrs['level'] ?? 2);
        $level = $level === 3 ? 3 : 2;

        return $this->wrap("h{$level}", $this->renderChildren($node), $this->alignmentAttribute($node));
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderImage(array $node): string
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $alt = $this->stringValue($attrs['alt'] ?? null);
        $src = $this->stringValue($attrs['src'] ?? null);

        if ($this->isFacebookEmojiSource($src)) {
            return $alt === '' ? '' : '<span class="article-emoji" role="img" aria-label="'.$this->attribute($alt).'">'.e($alt).'</span>';
        }

        $imageName = $this->stringValue($attrs['imageName'] ?? ($attrs['image_name'] ?? null));

        if ($this->isKnownStaticImage($imageName)) {
            return $this->renderStaticImage($imageName, $alt);
        }

        return $alt === ''
            ? '<span class="article-removed-image">Η εικόνα αφαιρέθηκε.</span>'
            : '<span class="article-removed-image">Εικόνα: '.e($alt).'</span>';
    }

    private function renderStaticImage(string $imageName, string $alt): string
    {
        $manifest = $this->imageManifest();
        $metadata = $manifest['images'][$imageName];
        $basePath = rtrim($this->stringValue($manifest['basePath'] ?? null), '/');
        $widths = array_values(array_filter(
            is_array($metadata['widths'] ?? null) ? $metadata['widths'] : [],
            fn (mixed $width): bool => is_int($width) || ctype_digit((string) $width),
        ));
        $widths = array_map('intval', $widths);
        $fallbackWidth = $widths[array_key_last($widths)];
        $srcset = collect($widths)
            ->map(fn (int $width): string => "{$basePath}/{$imageName}-{$width}.webp {$width}w")
            ->implode(', ');

        return '<img class="article-inline-image" src="'.$this->attribute("{$basePath}/{$imageName}-{$fallbackWidth}.webp").'" srcset="'.$this->attribute($srcset).'" sizes="(min-width: 768px) 48rem, 100vw" width="'.(int) $metadata['width'].'" height="'.(int) $metadata['height'].'" alt="'.$this->attribute($alt).'" loading="lazy" decoding="async">';
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderChildren(array $node): string
    {
        $children = is_array($node['content'] ?? null) ? $node['content'] : [];

        return collect($children)
            ->map(fn (mixed $child): string => is_array($child) ? $this->renderNode($child) : '')
            ->implode('');
    }

    private function wrap(string $tag, string $html, string $attributes = ''): string
    {
        return "<{$tag}{$attributes}>{$html}</{$tag}>";
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function alignmentAttribute(array $node): string
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $textAlign = $this->stringValue($attrs['textAlign'] ?? null);

        if (! in_array($textAlign, ['left', 'center', 'right', 'justify'], true)) {
            return '';
        }

        return ' style="text-align:'.$textAlign.'"';
    }

    private function safeWebUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || preg_match('/[\x00-\x20\x7f]/', $url) === 1) {
            return null;
        }

        $parts = parse_url($url);
        $scheme = strtolower($this->stringValue($parts['scheme'] ?? null));

        if (! is_array($parts) || ! in_array($scheme, ['http', 'https'], true) || ! isset($parts['host'])) {
            return null;
        }

        return $url;
    }

    private function isFacebookEmojiSource(string $src): bool
    {
        $parts = parse_url($src);

        if (! is_array($parts)) {
            return false;
        }

        $host = strtolower($this->stringValue($parts['host'] ?? null));
        $path = $this->stringValue($parts['path'] ?? null);

        return in_array($host, ['static.xx.fbcdn.net', 'www.facebook.com', 'facebook.com'], true)
            && str_contains($path, '/images/emoji.php');
    }

    private function isKnownStaticImage(string $imageName): bool
    {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $imageName) !== 1) {
            return false;
        }

        $manifest = $this->imageManifest();
        $metadata = $manifest['images'][$imageName] ?? null;

        return is_array($metadata)
            && isset($metadata['width'], $metadata['height'])
            && is_array($metadata['widths'] ?? null)
            && $metadata['widths'] !== [];
    }

    /**
     * @return array<string, mixed>
     */
    private function imageManifest(): array
    {
        if ($this->imageManifest !== null) {
            return $this->imageManifest;
        }

        $path = config('images.static.manifest_path');

        if (! is_string($path) || ! File::isFile($path)) {
            throw new RuntimeException('Το αρχείο εικόνων δεν είναι διαθέσιμο.');
        }

        $manifest = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($manifest)) {
            throw new RuntimeException('Το αρχείο εικόνων δεν είναι έγκυρο.');
        }

        return $this->imageManifest = $manifest;
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function attribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
