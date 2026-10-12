<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Package;
use App\Models\PressRelease;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Public blog: /blogs (listing with category filter, search and pagination) and /blogs/{slug}.
 * Posts are stored in the press_releases table (the section was the "Newsroom" until October 2026).
 */
class BlogController extends Controller
{
    public const PER_PAGE = 6;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(PressRelease::CATEGORIES)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $posts = PressRelease::published()
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like));
            })
            ->latestFirst()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Categories with at least one published post, in the configured order, with their counts.
        $counts = PressRelease::published()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category');
        $categories = collect(PressRelease::CATEGORIES)->filter(fn ($c) => isset($counts[$c]))
            ->mapWithKeys(fn ($c) => [$c => (int) $counts[$c]])->all();

        $recent = PressRelease::published()->latestFirst()->limit(4)->get();

        return view('public.blogs.index', [
            'posts' => $posts,
            'featured' => $filters === [] ? $recent->first() : null,
            'categories' => $categories,
            'filters' => $filters,
            'recent' => $recent,
        ] + $this->packages());
    }

    public function show(PressRelease $release): View
    {
        abort_unless(
            $release->is_published && $release->published_at !== null && $release->published_at->isPast(),
            404,
        );

        // Same category first, then the newest. The first four go in the sidebar, the next three in "More from our blog".
        $others = PressRelease::published()->whereKeyNot($release->id)
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$release->category])
            ->latestFirst()
            ->limit(7)
            ->get();

        return view('public.blogs.show', [
            'post' => $release,
            'related' => $others->take(4),
            'more' => $others->slice(4)->values(),
            'readingMinutes' => $release->reading_minutes,
            // General FAQs (not tied to a package) shown under every article.
            'faqs' => Faq::active()->whereNull('package_id')->ordered()->limit(5)->get(),
        ] + $this->packages());
    }

    /**
     * Packages for the "Compare packages" slider shown under the blog content.
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
