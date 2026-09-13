<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'DENY');

        // The dev server loads modules and its own websocket from another origin,
        // so a policy written for the built assets would only break `npm run dev`
        // without telling us anything about what ships.
        if (! Vite::isRunningHot()) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",

            // There is not one inline script in the application: Inertia passes the
            // page through a data attribute and Vite emits files. This is the half
            // of the policy that is worth having, so it stays without an escape.
            "script-src 'self'",

            // ProseMirror inserts a stylesheet of its own when the editor mounts,
            // which no nonce of ours would carry.
            "style-src 'self' 'unsafe-inline'",

            // data: is the grain overlay, an inline SVG in the stylesheet.
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",

            // Only reached after the visitor clicks play, and only ever this host.
            'frame-src https://www.youtube-nocookie.com',
        ]).';';
    }
}
