<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
            'highlighted' => CatalogCache::highlightedOutlets(),
            'mediaSummary' => CatalogCache::mediaSummary(),
        ]);
    }
}
