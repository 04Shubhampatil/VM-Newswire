<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaOutlet;
use App\Models\Package;
use App\Support\CatalogCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PackageMediaController extends Controller
{
    /** Featured platforms shown prominently on the package page. */
    public const MAX_FEATURED = 7;

    public function store(Request $request, Package $package): RedirectResponse
    {
        $data = $request->validate([
            'media_ids' => ['array'],
            'media_ids.*' => ['integer', Rule::exists('media_outlets', 'id')],
            'category' => ['nullable', Rule::in(config('vmnewswire.media_categories'))],
            'is_featured' => ['boolean'],
        ]);

        $ids = collect($data['media_ids'] ?? []);
        if (! empty($data['category'])) {
            $ids = $ids->merge(MediaOutlet::active()->where('category', $data['category'])->pluck('id'));
        }

        $ids = $ids->unique()->diff($package->mediaOutlets()->pluck('media_outlets.id'))->values();
        $featured = (bool) ($data['is_featured'] ?? false);

        if ($ids->isEmpty()) {
            return back()->with('toast_error', 'Select at least one outlet that is not already in this package.');
        }

        if ($featured && $package->featuredMedia()->count() + $ids->count() > self::MAX_FEATURED) {
            return back()->with('toast_error', 'A package can have at most '.self::MAX_FEATURED.' featured platforms.');
        }

        $order = (int) DB::table('package_media')->where('package_id', $package->id)->max('display_order');
        $package->mediaOutlets()->attach(
            $ids->mapWithKeys(fn ($id) => [$id => ['is_featured' => $featured, 'display_order' => ++$order]])->all()
        );

        CatalogCache::flush();

        return back()->with('toast', $ids->count().' outlet(s) added '.($featured ? 'as featured.' : 'to the network.'));
    }

    public function update(Request $request, Package $package, MediaOutlet $media): RedirectResponse
    {
        $action = $request->validate(['action' => ['required', Rule::in(['feature', 'unfeature', 'up', 'down'])]])['action'];

        $pivot = $package->mediaOutlets()->whereKey($media->id)->firstOrFail()->pivot;

        if ($action === 'feature') {
            if ($package->featuredMedia()->count() >= self::MAX_FEATURED) {
                return back()->with('toast_error', 'A package can have at most '.self::MAX_FEATURED.' featured platforms.');
            }
            $package->mediaOutlets()->updateExistingPivot($media->id, ['is_featured' => true]);
        } elseif ($action === 'unfeature') {
            $package->mediaOutlets()->updateExistingPivot($media->id, ['is_featured' => false]);
        } else {
            $this->move($package, $media, (bool) $pivot->is_featured, $action);
        }

        CatalogCache::flush();

        return back()->with('toast', 'Package media updated.');
    }

    public function destroy(Package $package, MediaOutlet $media): RedirectResponse
    {
        $package->mediaOutlets()->detach($media->id);
        CatalogCache::flush();

        return back()->with('toast', "{$media->name} removed from this package.");
    }

    private function move(Package $package, MediaOutlet $media, bool $featured, string $direction): void
    {
        DB::transaction(function () use ($package, $media, $featured, $direction) {
            $rows = DB::table('package_media')
                ->where('package_id', $package->id)
                ->where('is_featured', $featured)
                ->orderBy('display_order')->orderBy('id')
                ->get(['id', 'media_outlet_id'])->values();

            $index = $rows->search(fn ($row) => (int) $row->media_outlet_id === $media->id);
            $swap = $direction === 'up' ? $index - 1 : $index + 1;

            if ($index === false || $swap < 0 || $swap >= $rows->count()) {
                return;
            }

            [$rows[$index], $rows[$swap]] = [$rows[$swap], $rows[$index]];

            foreach ($rows as $i => $row) {
                DB::table('package_media')->where('id', $row->id)->update(['display_order' => $i + 1]);
            }
        });
    }
}
