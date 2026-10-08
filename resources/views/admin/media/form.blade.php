@php $editing = $outlet->exists; @endphp
<x-layouts.admin :title="$editing ? $outlet->name : 'New media outlet'" :description="$editing ? 'Edit the outlet, its poster and logo.' : 'Add a publication or newswire to the media network.'" :breadcrumbs="[['Media Network', route('admin.media.index')], [$editing ? 'Edit' : 'New', null]]">
    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.media.update', $outlet) : route('admin.media.store') }}" class="flex flex-col gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="flex flex-col gap-6">
                <x-admin.panel title="Outlet details">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-admin.input name="name" label="Outlet name" :value="$outlet->name" required maxlength="255" />
                        <x-admin.select name="category" label="Category" :value="$outlet->category" :options="array_combine(config('vmnewswire.media_categories'), config('vmnewswire.media_categories'))" />
                    </div>
                    <x-admin.input name="short_description" label="Short description" :value="$outlet->short_description" maxlength="160" help="One line, shown on the homepage card and in the outlet dialog." />
                    <x-admin.textarea name="description" label="Description" :value="$outlet->description" rows="4" maxlength="2000" help="Optional longer text for the outlet dialog." />
                    <x-admin.input name="website_url" label="Website URL" type="url" :value="$outlet->website_url" placeholder="https://" />
                </x-admin.panel>

                <x-admin.panel id="poster" title="Poster image" description="Shown on the homepage news cards and in the outlet dialog. Uploads are optimised to WebP." :x-data="'posterPicker('.Js::from($outlet->poster_src).')'">
                    <div class="grid gap-5 md:grid-cols-[240px_1fr]">
                        <div class="flex flex-col gap-2">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-[10px] border border-line bg-canvas-deep">
                                <template x-if="preview"><img :src="preview" alt="Poster preview" class="size-full object-cover"></template>
                                <template x-if="! preview"><div class="flex size-full items-center justify-center text-[13px] text-muted">No poster yet</div></template>
                            </div>
                            <span class="text-[12px] font-medium text-muted" x-text="preview && preview !== current ? 'New poster (saved on submit)' : (current ? 'Current poster' : '')"></span>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div>
                                <label for="poster-file" class="admin-label">{{ $outlet->poster_src ? 'Replace poster' : 'Upload poster' }}</label>
                                <input id="poster-file" type="file" name="poster" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)" class="admin-input">
                                <p class="admin-help">JPG, PNG or WebP, at least {{ config('vmnewswire.posters.min_width') }}×{{ config('vmnewswire.posters.min_height') }}px, up to {{ round(config('vmnewswire.posters.max_kb') / 1024) }} MB.</p>
                                <p class="admin-help text-danger" x-show="error" x-text="error"></p>
                                @error('poster')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" x-show="preview && preview !== current" @click="cancel()" class="btn btn-secondary btn-sm">Cancel new image</button>
                                @if ($outlet->poster_src)
                                    <label class="flex h-9 items-center gap-2 text-[13px]"><input type="hidden" name="remove_poster" value="0"><input type="checkbox" name="remove_poster" value="1" x-model="remove" @change="if (remove) cancel(true)" class="size-4 accent-navy-900"> Remove current poster</label>
                                @endif
                            </div>
                            <p class="text-[12px] text-muted">The file is only uploaded when you save. The old poster is deleted only after the new one is stored.</p>
                        </div>
                    </div>
                </x-admin.panel>

                <x-admin.panel title="Logo" description="Optional. Shown on the homepage news card badge and on package pages. An uploaded file takes priority over the URL.">
                    <x-admin.input name="logo_url" label="Logo URL (https)" type="url" :value="$outlet->logo_url" placeholder="https://" />
                    <div>
                        <label for="logo" class="admin-label">Upload logo</label>
                        @if ($outlet->logo_src)
                            <div class="mb-3 flex items-center gap-4 rounded-[10px] border border-line p-3">
                                <img src="{{ $outlet->logo_src }}" alt="{{ $outlet->name }} logo" class="h-10 max-w-40 object-contain">
                                @if ($outlet->logo_path)
                                    <label class="flex items-center gap-2 text-[13px]"><input type="hidden" name="remove_logo" value="0"><input type="checkbox" name="remove_logo" value="1" class="size-4 accent-navy-900"> Remove uploaded logo</label>
                                @endif
                            </div>
                        @endif
                        <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="admin-input">
                        <p class="admin-help">PNG, JPG or WebP, up to 1 MB. Crop tightly to the logo artwork.</p>
                        @error('logo')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                    </div>
                </x-admin.panel>
            </div>

            <div class="flex flex-col gap-6 xl:sticky xl:top-24">
                <x-admin.panel title="Visibility">
                    <x-admin.toggle name="is_active" label="Enabled" help="Available on the website" :checked="$outlet->is_active" />
                    <x-admin.toggle name="is_highlighted" label="Highlight on homepage" help="Shown in the news cards and logo strip" :checked="$outlet->is_highlighted" />
                    <x-admin.input name="display_order" label="Display order" type="number" min="0" :value="$outlet->display_order ?? 0" help="Lower numbers appear first." />
                    @if ($editing)<p class="text-[13px] text-muted">Used by {{ $outlet->packages_count }} package(s).</p>@endif
                </x-admin.panel>
            </div>
        </div>

        <x-admin.form-actions>
            <a href="{{ route('admin.media.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />{{ $editing ? 'Save changes' : 'Create outlet' }}</button>
        </x-admin.form-actions>
    </form>
</x-layouts.admin>
