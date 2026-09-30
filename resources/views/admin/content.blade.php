<x-layouts.admin title="Website Content" breadcrumb="Admin / Website content">
    <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="flex max-w-5xl flex-col gap-6">
        @csrf @method('PUT')
        <p class="text-sm text-muted">Long text fields support Markdown: <code class="font-mono">**bold**</code>, <code class="font-mono">*italic*</code>, <code class="font-mono">## Heading</code>, <code class="font-mono">- list item</code>, <code class="font-mono">[link](https://…)</code>. HTML is removed for safety.</p>
        <section class="admin-panel flex flex-col gap-5">
            <h2 class="text-lg font-semibold">About page</h2>
            <x-admin.textarea name="about_intro" label="Introduction" :value="$settings->get('about_intro')" rows="3" required />
            <x-admin.textarea name="about_body" label="Body" :value="$settings->get('about_body')" rows="6" />
            @php $aboutImageUrl = $settings->get('about_image') ? Storage::disk(config('vmnewswire.posters.disk'))->url($settings->get('about_image')) : null; @endphp
            <div class="grid gap-5 md:grid-cols-[220px_1fr]" x-data="posterPicker(@js($aboutImageUrl))">
                <div class="flex flex-col gap-2">
                    <span class="admin-label" x-text="preview && preview !== current ? 'New photo (preview)' : (current ? 'Current photo' : 'No photo yet')"></span>
                    <div class="aspect-[4/3] overflow-hidden rounded-[6px] border border-[#e6e4ef] bg-canvas-deep">
                        <template x-if="preview"><img :src="preview" alt="About page photo preview" class="size-full object-cover"></template>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <label for="about_image" class="admin-label">About page photo</label>
                    <input id="about_image" type="file" name="about_image" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)" class="admin-input py-2 file:mr-3 file:rounded file:border-0 file:bg-[#EFEDF6] file:px-3 file:py-1 file:text-sm">
                    <p class="text-xs text-muted">JPG, PNG or WebP, at least {{ config('vmnewswire.posters.min_width') }}×{{ config('vmnewswire.posters.min_height') }}px, up to {{ round(config('vmnewswire.posters.max_kb') / 1024) }} MB. Shown beside "How we work"; 4:3 works best.</p>
                    <p class="text-xs text-danger" x-show="error" x-text="error"></p>
                    @error('about_image')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                    <div class="flex flex-wrap gap-3">
                        <button type="button" x-show="preview && preview !== current" @click="cancel()" class="btn btn-secondary btn-sm">Cancel new photo</button>
                        @if ($aboutImageUrl)
                            <label class="flex h-11 items-center gap-2 text-sm"><input type="hidden" name="remove_about_image" value="0"><input type="checkbox" name="remove_about_image" value="1" x-model="remove" @change="if (remove) cancel(true)" class="size-4 accent-accent-fill"> Remove photo</label>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        <section class="admin-panel flex flex-col gap-5">
            <h2 class="text-lg font-semibold">Footer</h2>
            <x-admin.textarea name="footer_text" label="Footer description" :value="$settings->get('footer_text')" rows="2" required maxlength="300" />
        </section>
        <section class="admin-panel flex flex-col gap-5">
            <h2 class="text-lg font-semibold">Privacy policy</h2>
            <x-admin.input name="privacy_updated_at" label="Last updated" type="date" :value="$settings->all()['privacy_updated_at']" class="max-w-xs" />
            <x-admin.textarea name="privacy_content" label="Content" :value="$settings->all()['privacy_content']" rows="14" mono />
        </section>
        <section class="admin-panel flex flex-col gap-5">
            <h2 class="text-lg font-semibold">Terms &amp; conditions</h2>
            <x-admin.input name="terms_updated_at" label="Last updated" type="date" :value="$settings->all()['terms_updated_at']" class="max-w-xs" />
            <x-admin.textarea name="terms_content" label="Content" :value="$settings->all()['terms_content']" rows="14" mono />
        </section>
        <button type="submit" class="btn btn-primary btn-sm self-start">Save content</button>
    </form>

    <section id="faqs" class="flex max-w-5xl flex-col gap-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">FAQs</h2>
            <x-button :href="route('admin.faqs.create')" size="sm" icon-left="plus">Add FAQ</x-button>
        </div>
        <x-admin.data-table caption="FAQs">
            <x-slot:head><th>Question</th><th>Category</th><th>Shown on</th><th>Status</th><th class="text-right">Actions</th></x-slot:head>
            @forelse ($faqs as $faq)
                <tr>
                    <td class="font-medium">{{ $faq->question }}</td>
                    <td>{{ $faq->category }}</td>
                    <td class="text-muted">{{ $faq->package?->name ?? 'FAQ page' }}</td>
                    <td><x-admin.status-badge :status="$faq->is_active ? 'active' : 'inactive'" /></td>
                    <td>
                        <div class="flex justify-end gap-4 text-sm font-semibold">
                            <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-accent-ink hover:underline">Edit</a>
                            <x-admin.confirm-form :action="route('admin.faqs.destroy', $faq)" method="DELETE" confirm="Delete this FAQ?">
                                <button type="submit" class="text-danger hover:underline">Delete</button>
                            </x-admin.confirm-form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-muted">No FAQs yet.</td></tr>
            @endforelse
        </x-admin.data-table>
    </section>
</x-layouts.admin>
