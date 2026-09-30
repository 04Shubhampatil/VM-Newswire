<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Package;
use App\Models\PackageSlugRedirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        $packages = Package::active()->ordered()
            ->with(['featuredMedia:media_outlets.id,media_outlets.name', 'currentReport'])
            ->get();

        return view('public.packages.index', [
            'packages' => $packages,
            'brands' => $packages->pluck('brand')->filter()->unique()->values(),
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $package = Package::active()->where('slug', $slug)
            ->with(['featuredMedia', 'currentReport', 'faqs'])
            ->withCount('networkMedia')
            ->first();

        if (! $package) {
            // Old slug after a rename → permanent redirect to the current URL.
            $redirect = PackageSlugRedirect::where('old_slug', $slug)
                ->whereHas('package', fn ($q) => $q->active())
                ->with('package:id,slug')
                ->first();

            abort_unless($redirect, 404);

            return redirect()->route('packages.show', $redirect->package->slug, 301);
        }

        $faqs = $package->faqs->isNotEmpty()
            ? $package->faqs
            : Faq::active()->whereNull('package_id')->whereIn('category', ['Packages', 'Sample reports', 'Enquiries'])->ordered()->limit(4)->get();

        return view('public.packages.show', [
            'package' => $package,
            'faqs' => $faqs,
            'packages' => Package::active()->ordered()->get(['id', 'name', 'slug', 'price', 'currency']),
        ]);
    }

    /**
     * Paginated extended network for the "View full distribution network" section.
     */
    public function network(Request $request, Package $package): JsonResponse
    {
        abort_unless($package->is_active, 404);

        $search = trim((string) $request->query('search', ''));

        $outlets = $package->networkMedia()
            ->select(['media_outlets.id', 'media_outlets.name', 'media_outlets.category'])
            ->when($search !== '', fn ($q) => $q->where('media_outlets.name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->reorder('media_outlets.name')
            ->paginate(48)
            ->through(fn ($outlet) => ['name' => $outlet->name, 'category' => $outlet->category]);

        return response()->json($outlets);
    }
}
