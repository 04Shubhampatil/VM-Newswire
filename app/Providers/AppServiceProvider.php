<?php

namespace App\Providers;

use App\Models\MediaOutlet;
use App\Models\Package;
use App\Services\SiteSettings;
use App\Support\CatalogCache;
use App\View\Composers\AdminSidebarComposer;
use App\View\Composers\FooterComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Surface N+1 queries and silently discarded attributes during development.
        Model::shouldBeStrict(! $this->app->isProduction());

        Password::defaults(fn () => Password::min(12)->letters()->numbers());

        // Settings load lazily, so sharing the instance costs nothing until a view reads it.
        View::share('site', $this->app->make(SiteSettings::class));
        View::composer('components.footer', FooterComposer::class);
        View::composer('components.admin.sidebar', AdminSidebarComposer::class);

        foreach ([Package::class, MediaOutlet::class] as $model) {
            $model::saved(fn () => CatalogCache::flush());
            $model::deleted(fn () => CatalogCache::flush());
        }

        RateLimiter::for('enquiries', fn (Request $request) => [
            Limit::perMinute(config('vmnewswire.enquiries.per_minute'))->by('enquiry-min:'.$request->ip()),
            Limit::perDay(config('vmnewswire.enquiries.per_day'))->by('enquiry-day:'.$request->ip()),
        ]);

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by('login:'.strtolower((string) $request->input('email')).'|'.$request->ip()),
        ]);
    }
}
