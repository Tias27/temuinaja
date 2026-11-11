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
        $configuredHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $allowedHosts = [...config('security.allowed_hosts', []), $configuredHost];
        abort_unless(in_array($request->getHost(), $allowedHosts, true), 400);

        // HTTPS tunnels may connect to PHP over HTTP; use the configured public host.
        $https = $request->isSecure() || ($request->getHost() === $configuredHost && str_starts_with((string) config('app.url'), 'https://'));
        if ($https) {
            config(['session.secure' => true]);
        }

        Vite::useCspNonce();
        $scriptSources = "'self' 'nonce-".Vite::cspNonce()."'";
        $connectSources = "'self'";
        $styleSources = "'self' 'unsafe-inline'";
        if (app()->environment('local') && Vite::isRunningHot()) {
            $origin = trim(file_get_contents(Vite::hotFile()));
            if (preg_match('~^https?://(?:localhost|127\.0\.0\.1|\[::1\])(?::[0-9]+)?$~D', $origin)) {
                $scriptSources .= ' '.$origin;
                $styleSources .= ' '.$origin;
                $connectSources .= ' '.$origin.' '.preg_replace('~^http~', 'ws', $origin);
            }
        }

        $response = $next($request);
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src {$scriptSources}; style-src {$styleSources}; img-src 'self' data: blob:; font-src 'self' data:; connect-src {$connectSources}; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
        self::baseline($response);
        if ($https) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }
        if ($request->is('admin', 'admin/*', 'laporan/sukses/*') || $request->user('admin')) {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public static function baseline(Response $response): Response
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->remove('X-Powered-By');
        if ($response->getStatusCode() >= 400) {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
        }

        return $response;
    }
}
