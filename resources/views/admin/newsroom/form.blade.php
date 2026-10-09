@php
    $editing = $release->exists;
    $imageUrl = $release->image_url;
    $categories = array_combine(\App\Models\PressRelease::CATEGORIES, \App\Models\PressRelease::CATEGORIES);
@endphp
<x-layouts.admin :title="$editing ? 'Edit press release' : 'New press release'" :breadcrumbs="[['Newsroom', route('admin.newsroom.index')], [$editing ? 'Edit' : 'New', null]]">
    @if ($editing && $release->is_published)
        <x-slot:actions>
            <x-button :href="route('newsroom.show', $release->slug)" variant="secondary" size="sm" target="_blank" icon-left="external">View on website</x-button>
        </x-slot:actions>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.newsroom.update', $release) : route('admin.newsroom.store') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="flex flex-col gap-6">
                <x-admin.panel title="Press release">
                    <x-admin.input name="title" label="Title" :value="$release->title" required maxlength="200" />
                    <x-admin.textarea name="excerpt" label="Summary" :value="$release->excerpt" rows="3" maxlength="400" help="One or two sentences shown on the Newsroom cards and under the title. Optional: without it the start of the body is used." />
                    <x-admin.textarea name="body" label="Body" :value="$release->body" rows="18" required maxlength="60000" help="Markdown is supported: ## Heading, **bold**, bullet lists and links." />
                </x-admin.panel>

                <x-admin.panel title="Photo" description="Shown at the top of the article and on the Newsroom cards. 16:9 works best." :x-data="'posterPicker('.Js::from($imageUrl).')'">
                    <div class="grid gap-5 md:grid-cols-[280px_minmax(0,1fr)]">
                        <div class="flex flex-col gap-2">
                            <div class="aspect-video overflow-hidden rounded-[10px] border border-line bg-canvas-deep">
                                <template x-if="preview"><img :src="preview" alt="Press release photo preview" class="size-full object-cover"></template>
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
                <x-admin.input name="slug" label="URL slug" :value="$release->slug" maxlength="120" placeholder="created from the title" help="Letters, numbers and hyphens. /newsroom/your-slug" />
                <x-admin.toggle name="is_published" label="Published" help="Visible on the Newsroom page" :checked="$release->is_published" />
            </x-admin.panel>
        </div>

        <x-admin.form-actions>
            <a href="{{ route('admin.newsroom.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />{{ $editing ? 'Save press release' : 'Add press release' }}</button>
        </x-admin.form-actions>
    </form>
</x-layouts.admin>
