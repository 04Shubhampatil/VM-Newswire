@php $editing = $outlet->exists; @endphp
<x-layouts.admin :title="$editing ? $outlet->name : 'New media outlet'" :breadcrumb="'Admin / Media Network / '.($editing ? 'Edit' : 'Add')">
    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.media.update', $outlet) : route('admin.media.store') }}"
          class="grid max-w-5xl items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="flex flex-col gap-6">
            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Outlet details</h2>
                <div class="grid gap-5 md:grid-cols-2">
                    <x-admin.input name="name" label="Outlet name" :value="$outlet->name" required maxlength="255" />
                    <x-admin.select name="category" label="Category" :value="$outlet->category" :options="array_combine(config('vmnewswire.media_categories'), config('vmnewswire.media_categories'))" />
                </div>
                <x-admin.input name="short_description" label="Short description" :value="$outlet->short_description" maxlength="160" help="One line, shown in the outlet popup. Up to 160 characters." />
                <x-admin.textarea name="description" label="Description" :value="$outlet->description" rows="4" maxlength="2000" help="Optional longer text for the outlet popup." />
                <x-admin.input name="website_url" label="Website URL" type="url" :value="$outlet->website_url" placeholder="https://" />
            </section>

            {{-- Poster --}}
            <section class="admin-panel flex flex-col gap-5" x-data="posterPicker(@js($outlet->poster_src))">
                <div class="flex flex-col gap-1">
                    <h2 class="text-lg font-semibold">Poster image</h2>
                    <p class="text-sm text-muted">Shown on the home page distribution network. JPG, PNG or WebP, at least {{ config('vmnewswire.posters.min_width') }}×{{ config('vmnewswire.posters.min_height') }}px, up to {{ round(config('vmnewswire.posters.max_kb') / 1024) }} MB. Uploads are optimised to WebP at {{ config('vmnewswire.posters.max_width') }}px wide.</p>
                </div>
                <div class="grid gap-5 md:grid-cols-[220px_1fr]">
                    <div class="flex flex-col gap-2">
                        <span class="admin-label" x-text="preview && preview !== current ? 'New poster (preview)' : (current ? 'Current poster' : 'No poster yet')"></span>
                        <div class="relative aspect-[4/3] overflow-hidden rounded-[6px] border border-[#E6E4EF] bg-canvas">
                            <template x-if="preview"><img :src="preview" alt="Poster preview" class="size-full object-cover"></template>
                            <template x-if="! preview">
                                <div class="flex size-full flex-col justify-between p-4"><span class="size-2 bg-accent"></span><span class="font-display text-lg font-semibold">{{ $outlet->name ?: 'Outlet name' }}</span><span class="text-[10px] font-bold tracking-[0.12em] text-muted uppercase">Fallback shown on site</span></div>
                            </template>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div>
                            <label for="poster" class="admin-label">{{ $outlet->poster_src ? 'Replace poster' : 'Upload poster' }}</label>
                            <input id="poster" type="file" name="poster" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)"
                                   class="admin-input py-2 file:mr-3 file:rounded file:border-0 file:bg-[#EFEDF6] file:px-3 file:py-1 file:text-sm">
                            <p class="mt-1.5 text-xs text-danger" x-show="error" x-text="error"></p>
                            @error('poster')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" x-show="preview && preview !== current" @click="cancel()" class="btn btn-secondary btn-sm">Cancel new image</button>
                            @if ($outlet->poster_src)
                                <label class="flex h-11 items-center gap-2 text-sm"><input type="hidden" name="remove_poster" value="0"><input type="checkbox" name="remove_poster" value="1" x-model="remove" @change="if (remove) cancel(true)" class="size-4 accent-accent-fill"> Remove current poster</label>
                            @endif
                        </div>
                        <p class="text-xs text-muted">The file is only uploaded when you save. The old poster is deleted only after the new one is stored.</p>
                    </div>
                </div>
            </section>

            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Logo <span class="font-normal text-muted">(optional, used on package pages)</span></h2>
                <x-admin.input name="logo_url" label="Logo URL (https)" type="url" :value="$outlet->logo_url" placeholder="https://" help="Or upload a logo file. An uploaded logo takes priority." />
                <div>
                    <label for="logo" class="admin-label">Upload logo</label>
                    @if ($outlet->logo_src)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ $outlet->logo_src }}" alt="{{ $outlet->name }} logo" class="h-10 max-w-40 object-contain">
                            @if ($outlet->logo_path)
                                <label class="flex items-center gap-2 text-sm"><input type="hidden" name="remove_logo" value="0"><input type="checkbox" name="remove_logo" value="1" class="size-4 accent-accent-fill"> Remove uploaded logo</label>
                            @endif
                        </div>
                    @endif
                    <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="admin-input py-2 file:mr-3 file:rounded file:border-0 file:bg-[#EFEDF6] file:px-3 file:py-1 file:text-sm">
                    <p class="mt-1.5 text-xs text-muted">PNG, JPG or WebP, up to 1 MB.</p>
                    @error('logo')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </section>
        </div>

        <div class="flex flex-col gap-6 lg:sticky lg:top-24">
            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Visibility</h2>
                <x-admin.toggle name="is_active" label="Enabled" help="Available on the website" :checked="$outlet->is_active" />
                <x-admin.toggle name="is_highlighted" label="Show in hero network" help="Home page distribution visual and media strip (first 7 by order)" :checked="$outlet->is_highlighted" />
                <x-admin.input name="display_order" label="Display order" type="number" min="0" :value="$outlet->display_order ?? 0" help="Lower numbers appear first." />
                @if ($editing)<p class="text-sm text-muted">Used by {{ $outlet->packages_count }} package(s).</p>@endif
            </section>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary btn-sm grow">{{ $editing ? 'Save changes' : 'Create outlet' }}</button>
                <a href="{{ route('admin.media.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
        </div>
    </form>
</x-layouts.admin>
