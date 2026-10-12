@php
    $editing = $release->exists;
    $imageUrl = $release->image_url;
    $categories = array_combine(\App\Models\PressRelease::CATEGORIES, \App\Models\PressRelease::CATEGORIES);
@endphp
<x-layouts.admin :title="$editing ? 'Edit blog post' : 'New blog post'" :breadcrumbs="[['Blogs', route('admin.blogs.index')], [$editing ? 'Edit' : 'New', null]]">
    @if ($editing && $release->is_published)
        <x-slot:actions>
            <x-button :href="route('blogs.show', $release->slug)" variant="secondary" size="sm" target="_blank" icon-left="external">View on website</x-button>
        </x-slot:actions>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.blogs.update', $release) : route('admin.blogs.store') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="flex flex-col gap-6">
                <x-admin.panel title="Blog post">
                    <x-admin.input name="title" label="Title" :value="$release->title" required maxlength="200" />
                    <x-admin.textarea name="excerpt" label="Summary" :value="$release->excerpt" rows="3" maxlength="400" help="One or two sentences shown on the Blogs page and under the title. Optional: without it the start of the body is used." />
                    <x-admin.textarea name="body" label="Body" :value="$release->body" rows="18" maxlength="60000" help="Use the toolbar for headings, bold, lists, quotes and links, or type Markdown directly. The eye icon previews the article." />
                </x-admin.panel>

                <x-admin.panel title="Photo" description="Shown at the top of the article and on the Blogs page. 16:9 works best." :x-data="'posterPicker('.Js::from($imageUrl).')'">
                    <div class="grid gap-5 md:grid-cols-[280px_minmax(0,1fr)]">
                        <div class="flex flex-col gap-2">
                            <div class="aspect-video overflow-hidden rounded-[10px] border border-line bg-canvas-deep">
                                <template x-if="preview"><img :src="preview" alt="Blog post photo preview" class="size-full object-cover"></template>
                            </div>
                            <p class="text-[12px] font-medium text-muted" x-text="preview && preview !== current ? 'New photo (saved on submit)' : (preview ? 'Current photo' : 'No photo')"></p>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div>
                                <label for="image" class="admin-label">Upload photo</label>
                                <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)" class="admin-input">
                                <p class="admin-help">JPG, PNG or WebP up to {{ round(config('vmnewswire.posters.max_kb') / 1024) }} MB.</p>
                                <p class="admin-help text-danger" x-show="error" x-text="error"></p>
                                @error('image')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" x-show="preview && preview !== current" @click="cancel()" class="btn btn-secondary btn-sm">Cancel new photo</button>
                                @if ($imageUrl)
                                    <label class="flex h-9 items-center gap-2 text-[13px]"><input type="hidden" name="remove_image" value="0"><input type="checkbox" name="remove_image" value="1" x-model="remove" @change="if (remove) cancel(true)" class="size-4 accent-navy-900"> Remove photo</label>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-admin.panel>
            </div>

            <x-admin.panel title="Publishing">
                <x-admin.select name="category" label="Category" :value="$release->category" :options="$categories" />
                <x-admin.input name="author" label="Author / byline" :value="$release->author" maxlength="120" placeholder="VM Newswire" />
                <x-admin.input name="published_at" label="Publish date" type="datetime-local" :value="$release->published_at?->format('Y-m-d\TH:i')" help="A future date schedules the release." />
                <x-admin.input name="slug" label="URL slug" :value="$release->slug" maxlength="120" placeholder="created from the title" help="Letters, numbers and hyphens. /blogs/your-slug" />
                <x-admin.toggle name="is_published" label="Published" help="Visible on the Blogs page" :checked="$release->is_published" />
            </x-admin.panel>
        </div>

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <x-admin.panel title="SEO" description="Shown in search results and browser tabs. Empty fields fall back to the title and summary.">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-admin.input name="meta_title" label="SEO title" :value="$release->meta_title" maxlength="120" />
                    <x-admin.input name="meta_description" label="SEO description" :value="$release->meta_description" maxlength="320" />
                </div>
            </x-admin.panel>
        </div>

        <x-admin.form-actions>
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />{{ $editing ? 'Save blog post' : 'Add blog post' }}</button>
        </x-admin.form-actions>
    </form>

    {{-- Body editor: EasyMDE (Markdown with a toolbar and preview). Served from jsDelivr; the plain textarea still works without it. --}}
    @push('head')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.20.0/dist/easymde.min.css">
        <style>
            .EasyMDEContainer .CodeMirror { border: 1px solid var(--color-line); border-radius: 0 0 8px 8px; min-height: 420px; font-family: var(--font-sans); font-size: 15px; line-height: 1.65; color: var(--color-ink); }
            .EasyMDEContainer .CodeMirror-focused { border-color: var(--color-accent); box-shadow: 0 0 0 3px rgb(14 140 126 / .15); }
            .EasyMDEContainer .editor-toolbar { border: 1px solid var(--color-line); border-bottom: 0; border-radius: 8px 8px 0 0; background: #f8fafc; opacity: 1; }
            .EasyMDEContainer .editor-toolbar button { color: #334155; }
            .EasyMDEContainer .editor-toolbar button.active, .EasyMDEContainer .editor-toolbar button:hover { background: #fff; border-color: var(--color-line); }
            .EasyMDEContainer .editor-preview, .EasyMDEContainer .editor-preview-side { background: #fff; padding: 20px 24px; font-size: 15px; line-height: 1.7; color: #334155; }
            .EasyMDEContainer .editor-preview h2, .EasyMDEContainer .editor-preview-side h2 { margin: 24px 0 8px; font-size: 22px; font-weight: 700; color: var(--color-heading); }
            .EasyMDEContainer .editor-preview h3, .EasyMDEContainer .editor-preview-side h3 { margin: 20px 0 6px; font-size: 18px; font-weight: 700; color: var(--color-heading); }
            .EasyMDEContainer .editor-preview p, .EasyMDEContainer .editor-preview-side p { margin: 0 0 14px; }
            .EasyMDEContainer .editor-preview blockquote, .EasyMDEContainer .editor-preview-side blockquote { margin: 16px 0; padding: 12px 16px; border-left: 3px solid var(--color-accent); background: #f5f8fa; }
            .EasyMDEContainer .editor-statusbar { color: var(--color-muted); font-size: 12px; }
        </style>
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/easymde@2.20.0/dist/easymde.min.js" defer></script>
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                var textarea = document.getElementById('f-body');
                if (! textarea || typeof EasyMDE === 'undefined') return;
                new EasyMDE({
                    element: textarea,
                    autoDownloadFontAwesome: true,
                    spellChecker: false,
                    minHeight: '420px',
                    placeholder: 'Write the article here. Use the toolbar for headings, bold text, lists, quotes and links.',
                    status: ['words'],
                    toolbar: [
                        'bold', 'italic', '|',
                        { name: 'heading-2', action: EasyMDE.toggleHeading2, className: 'fa fa-header', title: 'Section heading' },
                        { name: 'heading-3', action: EasyMDE.toggleHeading3, className: 'fa fa-header fa-header-x fa-header-3', title: 'Sub-heading' }, '|',
                        'quote', 'unordered-list', 'ordered-list', '|',
                        'link', 'horizontal-rule', '|',
                        'preview', 'side-by-side', 'fullscreen', '|',
                        'undo', 'redo', '|', 'guide'
                    ],
                    renderingConfig: { singleLineBreaks: false },
                });
            });
        </script>
    @endpush
</x-layouts.admin>
