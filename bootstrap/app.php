<?php

use App\Http\Middleware\CachePublicPage;
use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'admin', 'password.changed'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'password.changed' => EnsurePasswordIsChanged::class,
            'page.cache' => CachePublicPage::class,
        ]);
        // The enquiry endpoint is CSRF-protected like every other POST. Cached pages carry a stale token, so the
        // form fetches a fresh one from GET /enquiries/token (uncached) before submitting.
        // Global (not just the web group) so error pages and the health check carry the headers too.
        $middleware->append(SecurityHeaders::class);
        // Hostinger's CDN terminates TLS in front of PHP: trust only the forwarded proto/port so secure cookies,
        // HSTS and https:// URLs work. X-Forwarded-For and X-Forwarded-Host are NOT trusted: LiteSpeed already
        // puts the real visitor IP in REMOTE_ADDR, and the CDN passes a client-supplied X-Forwarded-For through,
        // so trusting it let anyone fake their IP (bypassing the enquiry rate limit) or their host.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions); // No-op unless SENTRY_LARAVEL_DSN is set.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
