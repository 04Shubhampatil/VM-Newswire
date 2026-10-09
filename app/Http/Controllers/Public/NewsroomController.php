<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PressRelease;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsroomController extends Controller
{
    public const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(PressRelease::CATEGORIES)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $releases = PressRelease::published()
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like));
            })
            ->latestFirst()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Only categories that have at least one published release, in the configured order.
        $used = PressRelease::published()->distinct()->pluck('category')->all();
        $categories = array_values(array_filter(PressRelease::CATEGORIES, fn ($c) => in_array($c, $used, true)));

        return view('public.newsroom.index', [
            'releases' => $releases,
            'categories' => $categories,
            'filters' => $filters,
            'latest' => PressRelease::published()->latestFirst()->limit(5)->get(),
        ] + $this->packages());
    }

    public function show(PressRelease $release): View
    {
        abort_unless(
            $release->is_published && $release->published_at !== null && $release->published_at->isPast(),
            404,
        );

        // Same category first, then the newest. The first four go in the sidebar, the next three in "More from".
        $others = PressRelease::published()->whereKeyNot($release->id)
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$release->category])
            ->latestFirst()
            ->limit(7)
            ->get();

        $words = str_word_count(strip_tags((string) $release->body));

        return view('public.newsroom.show', [
            'release' => $release,
            'similar' => $others->take(4),
            'more' => $others->slice(4)->values(),
            'readingMinutes' => $words > 0 ? max(1, (int) ceil($words / 220)) : null,
        ] + $this->packages());
    }

    /**
     * Packages for the "Packages to choose from" section shown under the newsroom content.
     *
     * @return array{packages: \Illuminate\Database\Eloquent\Collection, brands: \Illuminate\Support\Collection}
     */
    private function packages(): array
    {
        $packages = Package::active()->ordered()
            ->with(['featuredMedia:media_outlets.id,media_outlets.name', 'currentReport'])
            ->get();

        return [
            'packages' => $packages,
            'brands' => $packages->pluck('brand')->filter()->unique()->values(),
        ];
    }
}
