@php $aboutImageUrl = $settings->get('about_image') ? Storage::disk(config('vmnewswire.posters.disk'))->url($settings->get('about_image')) : null; @endphp
<x-layouts.admin title="Website Content" description="Edit the copy and images shown on the public website. Changes go live as soon as you save." :breadcrumbs="[['Website Content', route('admin.content.edit')], ['About page', null]]">
    <x-slot:actions>
        <x-button :href="route('about')" variant="secondary" size="sm" target="_blank" icon-left="external">Preview page</x-button>
    </x-slot:actions>

    @include('admin.content.partials.tabs')

    <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
        @csrf @method('PUT')
        <input type="hidden" name="section" value="about">

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <x-admin.panel title="Copy" description="Markdown is supported in the body: **bold**, *italic*, ## headings, - lists and [links](https://…). HTML is removed for safety.">
                <x-admin.textarea name="about_intro" label="Introduction" :value="$settings->get('about_intro')" rows="3" required maxlength="600" />
                <x-admin.textarea name="about_body" label="Body" :value="$settings->get('about_body')" rows="12" maxlength="10000" />
            </x-admin.panel>

            <x-admin.panel title="Photo" description="Shown beside “How we work”. 4:3 works best." :x-data="'posterPicker('.Js::from($aboutImageUrl).')'">
                <div class="aspect-[4/3] overflow-hidden rounded-[10px] border border-line bg-canvas-deep">
                    <template x-if="preview"><img :src="preview" alt="About page photo preview" class="size-full object-cover"></template>
                    <template x-if="! preview"><div class="flex size-full items-center justify-center text-[13px] text-muted">No photo yet</div></template>
                </div>
                <p class="-mt-2 text-[12px] font-medium text-muted" x-text="preview && preview !== current ? 'New photo (saved on submit)' : (current ? 'Current photo' : '')"></p>
                <div>
                    <label for="about_image" class="admin-label">Upload photo</label>
                    <input id="about_image" type="file" name="about_image" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)" class="admin-input">
                    <p class="admin-help">JPG, PNG or WebP, at least {{ config('vmnewswire.posters.min_width') }}×{{ config('vmnewswire.posters.min_height') }}px, up to {{ round(config('vmnewswire.posters.max_kb') / 1024) }} MB.</p>
                    <p class="admin-help text-danger" x-show="error" x-text="error"></p>
                    @error('about_image')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" x-show="preview && preview !== current" @click="cancel()" class="btn btn-secondary btn-sm">Cancel new photo</button>
                    @if ($aboutImageUrl)
                        <label class="flex h-9 items-center gap-2 text-[13px]"><input type="hidden" name="remove_about_image" value="0"><input type="checkbox" name="remove_about_image" value="1" x-model="remove" @change="if (remove) cancel(true)" class="size-4 accent-navy-900"> Remove photo</label>
                    @endif
                </div>
            </x-admin.panel>
        </div>

        <x-admin.form-actions>
            <x-slot:note>Saving publishes the changes immediately.</x-slot:note>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />Save about page</button>
        </x-admin.form-actions>
    </form>
</x-layouts.admin>
