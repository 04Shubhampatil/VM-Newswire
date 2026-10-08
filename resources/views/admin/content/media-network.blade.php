@php $networkImageUrl = $settings->get('media_network_image') ? Storage::disk(config('vmnewswire.posters.disk'))->url($settings->get('media_network_image')) : null; @endphp
<x-layouts.admin title="Website Content" description="Edit the copy and images shown on the public website. Changes go live as soon as you save." :breadcrumbs="[['Website Content', route('admin.content.edit')], ['Media Network page', null]]">
    <x-slot:actions>
        <x-button :href="route('media-network')" variant="secondary" size="sm" target="_blank" icon-left="external">Preview page</x-button>
    </x-slot:actions>

    @include('admin.content.partials.tabs')

    <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
        @csrf @method('PUT')
        <input type="hidden" name="section" value="media-network">

        <x-admin.panel title="1 · Hero copy" description="The headline block at the top of the page.">
            <div class="grid gap-5 md:grid-cols-[240px_minmax(0,1fr)]">
                <x-admin.input name="media_network_eyebrow" label="Eyebrow" :value="$settings->get('media_network_eyebrow')" maxlength="80" />
                <x-admin.input name="media_network_heading" label="Headline" :value="$settings->get('media_network_heading')" maxlength="160" help="Wrap a phrase in *asterisks* to colour it teal." />
            </div>
            <x-admin.textarea name="media_network_text" label="Intro text" :value="$settings->get('media_network_text')" rows="3" maxlength="500" help="{company} inserts the company name and {network} the network size, both from Settings." />
        </x-admin.panel>

        <x-admin.panel title="2 · Outlet directory" description="The heading above the search, category filters and outlet cards. The cards themselves come from Media Network.">
            <div class="grid gap-5 md:grid-cols-[240px_minmax(0,1fr)]">
                <x-admin.input name="directory_eyebrow" label="Eyebrow" :value="$settings->get('directory_eyebrow')" maxlength="80" />
                <x-admin.input name="directory_heading" label="Heading" :value="$settings->get('directory_heading')" maxlength="120" help="Wrap a phrase in *asterisks* to colour it teal." />
            </div>
            <x-admin.input name="directory_text" label="Line under the heading" :value="$settings->get('directory_text')" maxlength="300" help="Leave empty to use the default wording." />
            <x-admin.alert type="info">
                Each card's name, logo, category, description and packages are edited on the outlet itself.
                <x-slot:action><a href="{{ route('admin.media.index') }}" class="admin-link">Manage media outlets →</a></x-slot:action>
            </x-admin.alert>
        </x-admin.panel>

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <x-admin.panel title="3 · Hero photo" description="Shown beside “Where your press release can appear”. Without an upload the default newsroom photo is used. 4:3 works best." :x-data="'posterPicker('.Js::from($networkImageUrl ?? asset('images/media-network-hero.webp')).')'">
                <div class="grid gap-5 md:grid-cols-[280px_minmax(0,1fr)]">
                    <div class="flex flex-col gap-2">
                        <div class="aspect-[4/3] overflow-hidden rounded-[10px] border border-line bg-canvas-deep">
                            <template x-if="preview"><img :src="preview" alt="Media Network page photo preview" class="size-full object-cover"></template>
                        </div>
                        <p class="text-[12px] font-medium text-muted" x-text="preview && preview !== current ? 'New photo (saved on submit)' : '{{ $networkImageUrl ? 'Current photo' : 'Default photo' }}'"></p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div>
                            <label for="media_network_image" class="admin-label">Upload photo</label>
                            <input id="media_network_image" type="file" name="media_network_image" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)" class="admin-input">
                            <p class="admin-help">JPG, PNG or WebP, at least {{ config('vmnewswire.posters.min_width') }}×{{ config('vmnewswire.posters.min_height') }}px, up to {{ round(config('vmnewswire.posters.max_kb') / 1024) }} MB.</p>
                            <p class="admin-help text-danger" x-show="error" x-text="error"></p>
                            @error('media_network_image')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" x-show="preview && preview !== current" @click="cancel()" class="btn btn-secondary btn-sm">Cancel new photo</button>
                            @if ($networkImageUrl)
                                <label class="flex h-9 items-center gap-2 text-[13px]"><input type="hidden" name="remove_media_network_image" value="0"><input type="checkbox" name="remove_media_network_image" value="1" x-model="remove" @change="if (remove) cancel(true)" class="size-4 accent-navy-900"> Use the default photo again</label>
                            @endif
                        </div>
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel title="Category filters">
                <p class="text-[13px] leading-relaxed text-muted">The filter pills list every category that has at least one active outlet, so they update automatically when outlets are added or re-categorised.</p>
                <a href="{{ route('admin.media.index') }}" class="btn btn-secondary btn-sm self-start"><x-icon name="globe" :size="15" />Manage media outlets</a>
            </x-admin.panel>
        </div>

        <x-admin.form-actions>
            <x-slot:note>Saving publishes the changes immediately.</x-slot:note>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />Save media network page</button>
        </x-admin.form-actions>
    </form>
</x-layouts.admin>
