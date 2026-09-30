<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaOutletRequest;
use App\Models\MediaOutlet;
use App\Services\PosterImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class MediaOutletController extends Controller
{
    public function __construct(private PosterImage $posters) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(config('vmnewswire.media_categories'))],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'hero' => ['nullable', Rule::in(['1'])],
        ]);

        $outlets = MediaOutlet::query()
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', '%'.addcslashes($term, '%_\\').'%'))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when(($filters['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($filters['hero'] ?? null, fn ($q) => $q->where('is_highlighted', true))
            ->withCount('packages')
            ->orderByDesc('is_highlighted')
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        return view('admin.media.index', ['outlets' => $outlets, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('admin.media.form', ['outlet' => new MediaOutlet([
            'is_active' => true,
            'category' => 'Digital Media',
            'display_order' => (MediaOutlet::max('display_order') ?? 0) + 1,
        ])]);
    }

    public function store(MediaOutletRequest $request): RedirectResponse
    {
        $outlet = new MediaOutlet($request->safe()->except(['logo', 'remove_logo', 'poster', 'remove_poster']));
        $outlet->display_order ??= 0;
        $outlet->slug = MediaOutlet::uniqueSlug($outlet->name);

        try {
            $this->handleLogo($request, $outlet);
            $this->handlePoster($request, $outlet);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['poster' => $e->getMessage()]);
        }

        $outlet->save();

        return redirect()->route('admin.media.index')->with('toast', "Media outlet “{$outlet->name}” created.");
    }

    public function edit(MediaOutlet $media): View
    {
        return view('admin.media.form', ['outlet' => $media->loadCount('packages')]);
    }

    public function update(MediaOutletRequest $request, MediaOutlet $media): RedirectResponse
    {
        $media->fill($request->safe()->except(['logo', 'remove_logo', 'poster', 'remove_poster']));
        $media->display_order ??= 0;

        try {
            $this->handleLogo($request, $media);
            $this->handlePoster($request, $media);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['poster' => $e->getMessage()]);
        }

        $media->save();

        return redirect()->route('admin.media.index')->with('toast', "Media outlet “{$media->name}” updated.");
    }

    public function toggle(MediaOutlet $media): RedirectResponse
    {
        $media->update(['is_active' => ! $media->is_active]);

        return back()->with('toast', $media->is_active ? "“{$media->name}” enabled." : "“{$media->name}” disabled.");
    }

    /**
     * Only outlets not used by any package can be deleted; others should be disabled.
     * The outlet's own poster and logo files are removed with it.
     */
    public function destroy(MediaOutlet $media): RedirectResponse
    {
        $count = $media->packages()->count();

        if ($count > 0) {
            return back()->with('toast_error', "“{$media->name}” is used by {$count} package(s). Remove it from those packages or disable it instead.");
        }

        if ($media->logo_path) {
            Storage::disk(config('vmnewswire.media_logos.disk'))->delete($media->logo_path);
        }
        $this->posters->delete($media->poster_path);
        $media->delete();

        return redirect()->route('admin.media.index')->with('toast', "Media outlet “{$media->name}” deleted.");
    }

    private function handleLogo(MediaOutletRequest $request, MediaOutlet $outlet): void
    {
        $disk = Storage::disk(config('vmnewswire.media_logos.disk'));

        if (($request->boolean('remove_logo') || $request->hasFile('logo')) && $outlet->logo_path) {
            $disk->delete($outlet->logo_path);
            $outlet->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            // Random, server-generated filename; the original name is never used.
            $outlet->logo_path = $request->file('logo')->store(config('vmnewswire.media_logos.directory'), config('vmnewswire.media_logos.disk'));
        }
    }

    /**
     * New poster is stored first; the old file is only deleted once the replacement succeeded.
     */
    private function handlePoster(MediaOutletRequest $request, MediaOutlet $outlet): void
    {
        $old = $outlet->poster_path;

        if ($request->hasFile('poster')) {
            $stored = $this->posters->store($request->file('poster'), $outlet->slug ?: $outlet->name);
            $outlet->poster_path = $stored['path'];
            $outlet->poster_width = $stored['width'];
            $outlet->poster_height = $stored['height'];
            $this->posters->delete($old);
        } elseif ($request->boolean('remove_poster') && $old) {
            $outlet->poster_path = null;
            $outlet->poster_width = null;
            $outlet->poster_height = null;
            $this->posters->delete($old);
        }
    }
}
