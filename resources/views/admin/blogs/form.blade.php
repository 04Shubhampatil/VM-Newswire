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
                    <x-admin.textarea name="body" label="Body" :value="$release->body" rows="18" maxlength="60000" help="Use the toolbar for headings, bold, alignment, lists, quotes and links." />
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

        @php
            $faqRows = old('faqs', $release->faqs ?? []);
            $faqRows = array_values(array_map(fn ($r) => ['question' => (string) ($r['question'] ?? ''), 'answer' => (string) ($r['answer'] ?? '')], is_array($faqRows) ? $faqRows : []));
        @endphp
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <x-admin.panel title="FAQ for this post" description="Questions and answers shown under this article only. Leave empty to hide the FAQ section. Up to 10." x-data="{ rows: {{ Js::from($faqRows) }}, add() { if (this.rows.length < 10) this.rows.push({ question: '', answer: '' }) }, remove(i) { this.rows.splice(i, 1) } }">
                <div class="flex flex-col gap-4">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="rounded-[10px] border border-line bg-canvas-deep/40 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-[12px] font-semibold tracking-[0.08em] text-muted uppercase" x-text="'Question ' + (i + 1)"></span>
                                <button type="button" @click="remove(i)" class="text-[12px] font-semibold text-danger hover:underline">Remove</button>
                            </div>
                            <label class="admin-label" :for="'faq-q-' + i">Question</label>
                            <input type="text" :id="'faq-q-' + i" :name="'faqs[' + i + '][question]'" x-model="row.question" maxlength="200" class="admin-input" placeholder="e.g. How long should a press release be?">
                            <label class="admin-label mt-3" :for="'faq-a-' + i">Answer</label>
                            <textarea :id="'faq-a-' + i" :name="'faqs[' + i + '][answer]'" x-model="row.answer" rows="3" maxlength="1500" class="admin-input" placeholder="Short, plain-language answer."></textarea>
                        </div>
                    </template>
                    <p class="text-[13px] text-muted" x-show="rows.length === 0">No questions yet. This post will not show a FAQ section.</p>
                    <div>
                        <button type="button" @click="add()" x-show="rows.length < 10" class="btn btn-secondary btn-sm"><x-icon name="plus" :size="14" /> Add question</button>
                    </div>
                    @error('faqs')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                    @foreach ($errors->get('faqs.*') as $messages)
                        <p class="text-xs text-danger">{{ $messages[0] }}</p>
                    @endforeach
                </div>
            </x-admin.panel>
        </div>

        <x-admin.form-actions>
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />{{ $editing ? 'Save blog post' : 'Add blog post' }}</button>
        </x-admin.form-actions>
    </form>

    {{-- Body editor: Quill (visual editor with headings, bold, lists, quotes, links and text alignment).
         The article is stored as HTML and sanitised on output by App\Support\SafeHtml. Older Markdown posts are
         converted to HTML when opened. Served from jsDelivr; the plain textarea still works without it. --}}
    @push('head')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
        <style>
            #body-editor-wrap .ql-toolbar.ql-snow { border: 1px solid var(--color-line); border-radius: 8px 8px 0 0; background: #f8fafc; }
            #body-editor-wrap .ql-container.ql-snow { border: 1px solid var(--color-line); border-top: 0; border-radius: 0 0 8px 8px; background: #fff; font-family: var(--font-sans); font-size: 15px; }
            #body-editor { min-height: 480px; }
            #body-editor .ql-editor { min-height: 480px; padding: 20px 24px; line-height: 1.7; color: #334155; }
            #body-editor .ql-editor h2 { margin: 24px 0 8px; font-size: 19px; font-weight: 700; color: var(--color-heading); }
            #body-editor .ql-editor h3 { margin: 20px 0 6px; font-size: 17px; font-weight: 700; color: var(--color-heading); }
            #body-editor .ql-editor p { margin: 0 0 14px; }
            #body-editor .ql-editor blockquote { margin: 16px 0; padding: 12px 16px; border-left: 3px solid var(--color-accent); background: #f5f8fa; }
            #body-editor .ql-editor strong { font-weight: 600; color: var(--color-heading); }
            #body-editor .ql-editor.ql-blank::before { color: #94a3b8; font-style: normal; left: 24px; }
            .body-editor-active #f-body { display: none; }
        </style>
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" defer></script>
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                var textarea = document.getElementById('f-body');
                if (! textarea || typeof Quill === 'undefined') return;

                var wrap = document.createElement('div');
                wrap.id = 'body-editor-wrap';
                var mount = document.createElement('div');
                mount.id = 'body-editor';
                wrap.appendChild(mount);
                textarea.parentNode.insertBefore(wrap, textarea.nextSibling);
                textarea.parentNode.classList.add('body-editor-active');

                var quill = new Quill(mount, {
                    theme: 'snow',
                    placeholder: 'Write the article here. Use the toolbar for headings, bold text, alignment, lists, quotes and links.',
                    modules: {
                        toolbar: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote', 'link'],
                            ['clean'],
                        ],
                    },
                });

                // Existing content (HTML; older Markdown posts arrive already converted).
                var initial = @json(old('body') !== null ? (string) old('body') : ($release->exists ? (string) $release->editor_html : ''));
                if (initial) quill.clipboard.dangerouslyPasteHTML(initial, 'silent');

                // Keep the hidden textarea in sync so the HTML is what gets submitted.
                // Quill's semantic export turns ordinary spaces into &nbsp;, so they are restored here.
                var sync = function () { textarea.value = quill.getText().trim() ? quill.getSemanticHTML().replace(/&nbsp;/g, ' ') : ''; };
                quill.on('text-change', sync);
                textarea.form.addEventListener('submit', sync);
            });
        </script>
    @endpush
</x-layouts.admin>
