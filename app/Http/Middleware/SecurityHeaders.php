<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }

    /**
     * Build the Content-Security-Policy header.
     *
     * 'unsafe-inline' is required by Livewire 4 + Alpine.js (inline scripts/styles).
     * frame-ancestors supersedes X-Frame-Options in modern browsers.
     */
    protected function contentSecurityPolicy(): string
    {
        $fontsCss = 'https://fonts.googleapis.com';
        $fontsFiles = 'https://fonts.gstatic.com';

        $viteOrigin = $this->viteDevServerOrigin();
        $viteHost = $viteOrigin === null ? '' : ' '.$viteOrigin;

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'".$viteHost,
            "style-src 'self' 'unsafe-inline' {$fontsCss}".$viteHost,
            "img-src 'self' data: blob:",
            "font-src 'self' data: {$fontsFiles}",
            "connect-src 'self' ws: wss:".$viteHost,
            "media-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        // `upgrade-insecure-requests` would rewrite the plain-HTTP Vite dev server
        // URL to https and break every asset, so it is production-only.
        if ($viteOrigin === null) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }

    /**
     * Origin of the running Vite dev server, or null when not running hot.
     */
    protected function viteDevServerOrigin(): ?string
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) file_get_contents($hotFile));
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $origin = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }
}
