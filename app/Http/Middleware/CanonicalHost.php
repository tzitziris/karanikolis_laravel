<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHost
{
    /**
     * The site answers on more than one address: the old subdomain, the www
     * form of the domain, and whatever else is pointed at the same folder in
     * cPanel. Search engines would see each as a copy of the site, and a link
     * shared on the old address would never move. In production every page
     * read on another host is sent, permanently, to the same page on APP_URL.
     * So is a page read over plain http when APP_URL is https: cPanel's own
     * switch for that sits on a hidden subdomain and is easy to miss.
     *
     * Redirects in cPanel would do the same, but cPanel writes them into
     * public/.htaccess, which every deploy overwrites.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $target = $this->canonicalOrigin();

        if (
            $target === null
            || ! app()->environment('production')
            || ! $request->isMethodSafe()
            || (
                strcasecmp($request->getHost(), $target['host']) === 0
                && ($target['scheme'] !== 'https' || $request->isSecure())
            )
        ) {
            return $next($request);
        }

        return redirect()->away($target['origin'].$request->getRequestUri(), 301);
    }

    /**
     * @return array{host: string, scheme: string, origin: string}|null
     */
    private function canonicalOrigin(): ?array
    {
        $parts = parse_url((string) config('app.url'));
        $host = $parts['host'] ?? null;

        if (! is_string($host) || $host === '' || $host === 'localhost') {
            return null;
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return ['host' => $host, 'scheme' => $scheme, 'origin' => "{$scheme}://{$host}{$port}"];
    }
}
