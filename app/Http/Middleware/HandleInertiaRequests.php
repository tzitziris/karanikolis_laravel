<?php

namespace App\Http\Middleware;

use App\Services\PageMetadata;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
            ],
            // Shared rather than passed page by page, so a page added later
            // cannot quietly ship without a title or a description. A page that
            // knows more than the route name says overrides this key.
            'meta' => fn (): array => app(PageMetadata::class)->forRequest($request),
        ];
    }
}
