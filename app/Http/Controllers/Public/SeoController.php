<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $static = collect(['home', 'packages.index', 'media-network', 'sample-reports.index', 'about', 'contact', 'faq', 'privacy', 'terms'])
            ->map(fn ($name) => ['loc' => route($name), 'lastmod' => null]);

        $packages = Package::active()->ordered()->get(['slug', 'updated_at'])
            ->map(fn ($p) => ['loc' => route('packages.show', $p->slug), 'lastmod' => $p->updated_at?->toAtomString()]);

        return response()
            ->view('public.sitemap', ['urls' => $static->concat($packages)])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /login', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8')->header('Cache-Control', 'public, max-age=86400');
    }
}
