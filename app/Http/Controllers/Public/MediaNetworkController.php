<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MediaOutlet;
use App\Support\CatalogCache;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MediaNetworkController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(config('vmnewswire.media_categories'))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $outlets = MediaOutlet::active()
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', '%'.addcslashes($term, '%_\\').'%'))
            ->with(['packages' => fn ($q) => $q->active()->ordered()->select('packages.id', 'packages.name', 'packages.slug')])
            ->orderByDesc('is_highlighted')
            ->ordered()
            ->paginate(24)
            ->withQueryString()
            ->fragment('directory');

        return view('public.media-network', [
            'outlets' => $outlets,
            'summary' => CatalogCache::mediaSummary(),
            'filters' => $filters,
            'totalOutlets' => CatalogCache::mediaSummary()->sum('count'),
        ]);
    }
}
