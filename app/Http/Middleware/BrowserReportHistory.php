<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class BrowserReportHistory
{
    public const COOKIE = 'temuin_report_history';

    public function handle(Request $request, Closure $next): Response
    {
        // Laravel decrypts and authenticates this cookie before this route middleware runs.
        $token = $request->cookie(self::COOKIE);
        if (! is_string($token) || ! preg_match('/^[a-f0-9]{64}$/D', $token)) {
            $token = bin2hex(random_bytes(32));
        }
        $request->attributes->set('browser_history_hash', hash('sha256', $token));

        $response = $next($request);
        $response->headers->setCookie(new Cookie(
            self::COOKIE, $token, now()->addYear(), '/', null,
            $request->isSecure() || (bool) config('session.secure'), true, false, 'lax',
        ));
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
