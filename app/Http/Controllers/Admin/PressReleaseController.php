<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PressRelease;
use App\Services\PosterImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PressReleaseController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        return view('admin.blogs.index', [
            'releases' => PressRelease::query()
                ->when($status === 'published', fn ($q) => $q->where('is_published', true))
                ->when($status === 'draft', fn ($q) => $q->where('is_published', false))
                ->latestFirst()
                ->paginate(25)
                ->withQueryString(),
            'status' => $status,
            'counts' => [
                'all' => PressRelease::count(),
                'published' => PressRelease::where('is_published', true)->count(),
                'draft' => PressRelease::where('is_published', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.blogs.form', [
            'release' => new PressRelease(['is_published' => true, 'category' => 'Business', 'published_at' => now()]),
        ]);
    }

    public function store(Request $request, PosterImage $images): RedirectResponse
    {
        $release = new PressRelease($this->validated($request));
        $release->slug = PressRelease::uniqueSlug($request->input('slug') ?: $release->title);

        try {
            $this->applyImage($request, $release, $images);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['image' => $e->getMessage()]);
        }

        $release->save();

        return redirect()->to(route('admin.blogs.index'))->with('toast', 'Blog post added.');
    }

    public function edit(PressRelease $release): View
    {
        return view('admin.blogs.form', ['release' => $release]);
    }

    public function update(Request $request, PosterImage $images, PressRelease $release): RedirectResponse
    {
        $release->fill($this->validated($request));

        if ($request->filled('slug')) {
            $slug = Str::slug($request->input('slug'));
            $release->slug = $slug === $release->slug ? $slug : PressRelease::uniqueSlug($slug, $release->id);
        }

        try {
            $this->applyImage($request, $release, $images);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['image' => $e->getMessage()]);
        }

        $release->save();

        return redirect()->to(route('admin.blogs.index'))->with('toast', 'Blog post updated.');
    }

    public function destroy(PosterImage $images, PressRelease $release): RedirectResponse
    {
        $images->delete($release->image_path);
        $release->delete();

        return redirect()->to(route('admin.blogs.index'))->with('toast', 'Blog post deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'category' => ['required', Rule::in(PressRelease::CATEGORIES)],
            'author' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'body' => ['required', 'string', 'max:60000'],
            'meta_title' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'faqs' => ['nullable', 'array', 'max:10'],
            'faqs.*.question' => ['nullable', 'string', 'max:200'],
            'faqs.*.answer' => ['nullable', 'string', 'max:1500'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['boolean'],
            'image' => ['nullable', 'file', 'max:'.config('vmnewswire.posters.max_kb')],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        // Keep only rows with both a question and an answer; store null when there are none.
        $faqs = collect($data['faqs'] ?? [])
            ->map(fn ($row) => ['question' => trim((string) ($row['question'] ?? '')), 'answer' => trim((string) ($row['answer'] ?? ''))])
            ->filter(fn ($row) => $row['question'] !== '' && $row['answer'] !== '')
            ->values()
            ->all();
        $data['faqs'] = $faqs ?: null;
        unset($data['slug'], $data['image'], $data['remove_image']);

        return $data;
    }

    private function applyImage(Request $request, PressRelease $release, PosterImage $images): void
    {
        $old = $release->image_path;

        if ($request->hasFile('image')) {
            $release->image_path = $images->store($request->file('image'), 'blog-'.Str::limit(Str::slug($release->title), 40, ''))['path'];
            $images->delete($old);
        } elseif ($request->boolean('remove_image') && $old) {
            $images->delete($old);
            $release->image_path = null;
        }
    }
}
