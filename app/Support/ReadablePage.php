<?php

namespace App\Support;

use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class ReadablePage
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $fallback
     */
    public static function render(string $component, array $props, array $fallback): Response
    {
        if ($fallback === []) {
            throw new InvalidArgumentException('Readable Inertia pages require server-rendered fallback content.');
        }

        return Inertia::render($component, [
            ...$props,
            'readableFallback' => $fallback,
        ]);
    }
}
