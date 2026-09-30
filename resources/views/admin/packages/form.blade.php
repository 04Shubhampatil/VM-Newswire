@php $editing = $package->exists; @endphp
<x-layouts.admin :title="$editing ? $package->name : 'New package'" :breadcrumb="'Admin / Packages / '.($editing ? 'Edit' : 'Create')">
    <x-slot:actions>
        @if ($editing && $package->is_active)
            <x-button :href="route('packages.show', $package->slug)" variant="secondary" size="sm" target="_blank" icon-left="external">View live page</x-button>
        @endif
        <x-button type="submit" form="package-form" size="sm" icon-left="check">{{ $editing ? 'Save changes' : 'Create package' }}</x-button>
    </x-slot:actions>

    <form id="package-form" method="POST" action="{{ $editing ? route('admin.packages.update', $package) : route('admin.packages.store') }}"
          class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="flex flex-col gap-6">
            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Package details</h2>
                <div class="grid gap-5 md:grid-cols-2">
                    <x-admin.input name="name" label="Package name" :value="$package->name" required maxlength="150" />
                    <x-admin.input name="slug" label="URL slug" :value="$package->slug" mono maxlength="160"
                                   help="Leave empty to generate from the name. Changing it keeps the old URL redirecting." />
                    <x-admin.input name="brand" label="Network / brand (used for filters)" :value="$package->brand" maxlength="64" help="e.g. AccessWire, GlobeNewswire, MSN" />
                    <x-admin.input name="distribution_summary" label="Distribution summary" :value="$package->distribution_summary" maxlength="255" help="Shown in the comparison table." />
                </div>
                <x-admin.textarea name="short_description" label="Short description" :value="$package->short_description" rows="2" required maxlength="500" />
                <x-admin.textarea name="full_content" label="Full content" :value="$package->full_content" rows="8" maxlength="20000"
                                  help="Markdown supported: **bold**, *italic*, lists (- item), links [text](https://…), ## headings. HTML is removed for safety." />
                <x-admin.textarea name="features_text" label="What's included (one per line)" :value="implode(PHP_EOL, $package->features ?? [])" rows="5"
                                  help="Up to 10 lines. The first 4 appear on package cards." />
            </section>

            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">SEO</h2>
                <x-admin.input name="meta_title" label="Meta title" :value="$package->meta_title" maxlength="120" :help="'Default: '.($package->name ?: 'Package name').' Press Release Distribution | VM Newswire'" />
                <x-admin.textarea name="meta_description" label="Meta description" :value="$package->meta_description" rows="2" maxlength="320" help="Default: the short description." />
            </section>
        </div>

        <div class="flex flex-col gap-6">
            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Visibility</h2>
                <x-admin.toggle name="is_active" label="Active" help="Shown on the website" :checked="$package->is_active" />
                <x-admin.toggle name="is_highlighted" label="Most requested" help="Featured treatment (one package)" :checked="$package->is_highlighted" />
                <x-admin.input name="display_order" label="Display order" type="number" min="0" :value="$package->display_order" />
            </section>
            <section class="admin-panel flex flex-col gap-5">
                <h2 class="text-lg font-semibold">Pricing</h2>
                <div class="grid grid-cols-[1fr_100px] gap-3">
                    <x-admin.input name="price" label="Price per release" type="number" step="0.01" min="0" :value="$package->price" help="Empty = “Price on request”" />
                    <x-admin.select name="currency" label="Currency" :value="$package->currency" :options="array_combine(\App\Http\Requests\Admin\PackageRequest::CURRENCIES, \App\Http\Requests\Admin\PackageRequest::CURRENCIES)" />
                </div>
            </section>
        </div>
    </form>

    @if ($editing)
        {{-- Featured media --}}
        <section class="admin-panel flex flex-col gap-5">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-semibold">Featured media platforms <span class="font-normal text-muted">({{ $featured->count() }} of {{ \App\Http\Controllers\Admin\PackageMediaController::MAX_FEATURED }})</span></h2>
                <p class="text-sm text-muted">Shown prominently on the package page and as chips on package cards.</p>
            </div>
            @include('admin.packages.partials.media-list', ['items' => $featured, 'isFeatured' => true])
        </section>

        {{-- Network media --}}
        <section class="admin-panel flex flex-col gap-5">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-semibold">Extended network <span class="font-normal text-muted">({{ $network->count() }} outlets)</span></h2>
                <p class="text-sm text-muted">Listed in “View full distribution network”. For large lists, use <a href="{{ route('admin.media.import') }}" class="text-accent-ink underline">CSV import</a> and attach to this package.</p>
            </div>
            @include('admin.packages.partials.media-list', ['items' => $network, 'isFeatured' => false])
        </section>

        {{-- Add outlets --}}
        <section class="admin-panel flex flex-col gap-5" x-data="{ q: '' }">
            <h2 class="text-lg font-semibold">Add media outlets</h2>
            @if ($available->isEmpty())
                <p class="text-sm text-muted">Every active outlet is already in this package. <a href="{{ route('admin.media.create') }}" class="text-accent-ink underline">Create a new outlet</a>.</p>
            @else
                <form method="POST" action="{{ route('admin.packages.media.store', $package) }}" class="flex flex-col gap-4">
                    @csrf
                    <label class="flex h-11 items-center gap-2 rounded-[6px] border border-[#D6D2E6] px-3 text-muted">
                        <x-icon name="search" :size="16" /><span class="sr-only">Filter outlets</span>
                        <input type="search" x-model="q" placeholder="Filter {{ $available->count() }} available outlets" class="grow bg-transparent text-sm text-ink outline-none">
                    </label>
                    <div class="grid max-h-72 gap-x-6 overflow-y-auto rounded-[6px] border border-[#EFEDF6] p-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($available as $outlet)
                            <label x-show="! q || @js(mb_strtolower($outlet->name)).includes(q.toLowerCase())" class="flex items-center gap-2.5 py-1.5 text-sm">
                                <input type="checkbox" name="media_ids[]" value="{{ $outlet->id }}" class="size-4 accent-accent-fill">
                                <span class="grow">{{ $outlet->name }}</span><span class="text-xs text-muted">{{ $outlet->category }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <x-admin.select name="category" label="…or add every active outlet in a category" placeholder="—" :options="array_combine(config('vmnewswire.media_categories'), config('vmnewswire.media_categories'))" class="md:w-80" />
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 text-sm"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" class="size-4 accent-accent-fill"> Add as featured</label>
                            <button type="submit" class="btn btn-dark btn-sm">Add selected</button>
                        </div>
                    </div>
                </form>
            @endif
        </section>

        {{-- Sample report --}}
        <section class="admin-panel flex flex-col gap-5">
            <h2 class="text-lg font-semibold">Sample report</h2>
            @if ($package->currentReport)
                <div class="flex flex-col gap-3 rounded-[8px] border border-[#E6E4EF] p-4 sm:flex-row sm:items-center">
                    <span class="flex h-12 w-10 shrink-0 items-center justify-center rounded-[4px] bg-accent-soft text-accent-ink"><x-icon name="file" :size="20" /></span>
                    <div class="flex grow flex-col">
                        <span class="text-sm font-semibold">{{ $package->currentReport->file_name }}</span>
                        <span class="text-xs text-muted">{{ $package->currentReport->formatted_size }} · uploaded {{ $package->currentReport->uploaded_at->format('j M Y') }}</span>
                    </div>
                    <div class="flex gap-3 text-sm font-semibold">
                        <a href="{{ route('admin.sample-reports.download', $package->currentReport) }}" class="text-ink hover:underline">Preview</a>
                        <x-admin.confirm-form :action="route('admin.sample-reports.destroy', $package->currentReport)" method="DELETE" confirm="Remove this sample report?">
                            <button type="submit" class="text-danger hover:underline">Remove</button>
                        </x-admin.confirm-form>
                    </div>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.sample-reports.store') }}" enctype="multipart/form-data" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">
                <input type="hidden" name="return_to" value="package">
                <div class="grow">
                    <label for="report-file" class="admin-label">{{ $package->currentReport ? 'Replace with a new PDF' : 'Upload PDF' }}</label>
                    <input id="report-file" type="file" name="file" accept="application/pdf,.pdf" required class="admin-input py-2 file:mr-3 file:rounded file:border-0 file:bg-[#EFEDF6] file:px-3 file:py-1 file:text-sm">
                    <p class="mt-1.5 text-xs text-muted">PDF only, up to {{ round(config('vmnewswire.sample_reports.max_kb') / 1024) }} MB.</p>
                    @error('file')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-dark btn-sm"><x-icon name="upload" :size="16" />Upload</button>
            </form>
        </section>

        {{-- Danger zone --}}
        <section class="admin-panel flex flex-col gap-3 border-[#f1c9c4] sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold">Archive package</h2>
                <p class="text-sm text-muted">Removes it from the website. Existing enquiries keep their package and price snapshot. You can restore it later.</p>
            </div>
            <x-admin.confirm-form :action="route('admin.packages.destroy', $package)" method="DELETE" confirm="Archive this package? It will disappear from the website.">
                <button type="submit" class="btn btn-sm border border-danger/40 bg-white text-danger hover:bg-danger-soft">Archive</button>
            </x-admin.confirm-form>
        </section>
    @endif
</x-layouts.admin>
