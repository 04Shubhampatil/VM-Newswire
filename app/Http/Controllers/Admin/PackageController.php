<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\MediaOutlet;
use App\Models\Package;
use App\Support\CatalogCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        return view('admin.packages.index', [
            'packages' => Package::ordered()
                ->withCount(['enquiries', 'featuredMedia', 'networkMedia'])
                ->with('currentReport')
                ->get(),
            'archivedCount' => Package::onlyTrashed()->count(),
        ]);
    }

    public function archived(): View
    {
        return view('admin.packages.archived', [
            'packages' => Package::onlyTrashed()->withCount('enquiries')->latest('deleted_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.form', [
            'package' => new Package(['currency' => 'USD', 'is_active' => true, 'display_order' => (Package::max('display_order') ?? 0) + 1]),
        ]);
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $package = DB::transaction(function () use ($request) {
            $package = Package::create($request->packageData());
            $this->ensureSingleHighlight($package);

            return $package;
        });

        return redirect()->route('admin.packages.edit', $package)
            ->with('toast', 'Package created successfully. Now add its media outlets and sample report.');
    }

    public function edit(Package $package): View
    {
        $package->load(['mediaOutlets', 'currentReport']);
        $attachedIds = $package->mediaOutlets->modelKeys();

        return view('admin.packages.form', [
            'package' => $package,
            'featured' => $package->mediaOutlets->where('pivot.is_featured', true)->values(),
            'network' => $package->mediaOutlets->where('pivot.is_featured', false)->values(),
            'available' => MediaOutlet::active()->whereNotIn('id', $attachedIds)->ordered()->get(['id', 'name', 'category']),
        ]);
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        DB::transaction(function () use ($request, $package) {
            $package->update($request->packageData());
            $this->ensureSingleHighlight($package);
        });

        return redirect()->route('admin.packages.edit', $package)->with('toast', 'Package updated successfully.');
    }

    /**
     * Archives (soft-deletes) the package. Enquiries keep their link and snapshots.
     */
    public function destroy(Package $package): RedirectResponse
    {
        $package->update(['is_active' => false]);
        $package->delete();

        return redirect()->route('admin.packages.index')->with('toast', "“{$package->name}” archived. It no longer appears on the website.");
    }

    public function restore(int $id): RedirectResponse
    {
        $package = Package::onlyTrashed()->findOrFail($id);
        $package->restore();

        return redirect()->route('admin.packages.edit', $package)->with('toast', 'Package restored (inactive). Activate it when ready.');
    }

    public function toggle(Package $package): RedirectResponse
    {
        $package->update(['is_active' => ! $package->is_active]);

        return back()->with('toast', $package->is_active ? 'Package activated.' : 'Package deactivated.');
    }

    public function move(Package $package, string $direction): RedirectResponse
    {
        DB::transaction(function () use ($package, $direction) {
            // Normalise ordering to 1..n, then swap with the neighbour.
            $ordered = Package::ordered()->get(['id', 'display_order', 'name'])->values();
            $index = $ordered->search(fn ($p) => $p->id === $package->id);
            $swap = $direction === 'up' ? $index - 1 : $index + 1;

            if ($swap >= 0 && $swap < $ordered->count()) {
                [$ordered[$index], $ordered[$swap]] = [$ordered[$swap], $ordered[$index]];
            }

            foreach ($ordered as $i => $item) {
                Package::whereKey($item->id)->update(['display_order' => $i + 1]);
            }
        });

        CatalogCache::flush();

        return back()->with('toast', 'Package order updated.');
    }

    private function ensureSingleHighlight(Package $package): void
    {
        if ($package->is_highlighted) {
            Package::whereKeyNot($package->id)->where('is_highlighted', true)->update(['is_highlighted' => false]);
        }
    }
}
