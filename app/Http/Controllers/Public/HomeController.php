<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\MediaOutlet;
use App\Models\Package;
use App\Support\CatalogCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $packages = Package::active()->ordered()
            ->with(['featuredMedia:media_outlets.id,media_outlets.name', 'currentReport'])
            ->get();

        return view('public.home', [
            'packages' => $packages,
            'brands' => $packages->pluck('brand')->filter()->unique()->values(),
            'highlighted' => CatalogCache::highlightedOutlets(),
            'mediaSummary' => CatalogCache::mediaSummary(),
            'outletCount' => MediaOutlet::query()->where('is_active', true)->count(),
            'faqs' => Faq::query()->active()->whereNull('package_id')->ordered()->take(4)->get(),
        ]);
    }
}
