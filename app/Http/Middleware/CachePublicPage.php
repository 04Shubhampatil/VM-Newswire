<?php

namespace App\Http\Middleware;

use App\Support\PageCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Serves public GET pages from the page cache for anonymous visitors (see App\Support\PageCache).
 *
 * Skipped for signed-in users, non-GET requests, responses that are not plain 200 HTML, and requests
 * carrying validation errors or flash messages (the no-JS form fallback), so nothing personal is cached.
 */
class CachePublicPage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->cacheable($request)) {
            return $next($request);
        }

        $key = PageCache::key($request->fullUrl());

        try {
            $cached = Cache::get($key);
        } catch (Throwable) {
            $cached = null;
        }

        if (is_array($cached)) {
            return response($cached['body'], 200, $cached['headers'])->header('X-Page-Cache', 'hit');
        }

        $response = $next($request);

        $isHtml = str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')
            || str_starts_with((string) $response->headers->get('Content-Type'), 'application/xml');

        if ($response->getStatusCode() === 200 && $isHtml && $this->onlySessionCookie($response)) {
            try {
                Cache::put($key, [
                    'body' => $response->getContent(),
                    'headers' => ['Content-Type' => $response->headers->get('Content-Type')],
                ], PageCache::TTL_SECONDS);
            } catch (Throwable) {
                // Caching is best effort.
            }
            $response->headers->set('X-Page-Cache', 'miss');
        }

        return $response;
    }

    private function cacheable(Request $request): bool
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->wantsJson()) {
            return false;
        }
        if ($request->user()) {
            return false;
        }
        $session = $request->hasSession() ? $request->session() : null;
        if ($session && ($session->has('errors') || $session->has('status') || $session->has('toast') || $session->hasOldInput())) {
            return false;
        }

        return true;
    }

    /** The session cookie is harmless to drop from the cached copy; anything else means the page is personal. */
    private function onlySessionCookie(Response $response): bool
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if (! in_array($cookie->getName(), [config('session.cookie'), 'XSRF-TOKEN'], true)) {
                return false;
            }
        }

        return true;
    }
}
