@php $editing = $package->exists; @endphp
<x-layouts.admin :title="$editing ? $package->name : 'New package'" :description="$editing ? 'Edit the package, its media outlets and sample report.' : 'Create a distribution package. You can attach media outlets and a sample report after saving.'" :breadcrumbs="[['Packages', route('admin.packages.index')], [$editing ? 'Edit' : 'New', null]]">
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
            <x-admin.panel title="Package details">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-admin.input name="name" label="Package name" :value="$package->name" required maxlength="150" />
                    <x-admin.input name="slug" label="URL slug" :value="$package->slug" mono maxlength="160"
                                   help="Leave empty to generate from the name. Changing it keeps the old URL redirecting." />
                    <x-admin.input name="brand" label="Network / brand" :value="$package->brand" maxlength="64" help="Used for the filter tabs, e.g. AccessWire, GlobeNewswire, MSN." />
                    <x-admin.input name="distribution_summary" label="Distribution summary" :value="$package->distribution_summary" maxlength="255" help="One line, shown in the comparison table." />
                </div>
                <x-admin.textarea name="short_description" label="Short description" :value="$package->short_description" rows="2" required maxlength="500" help="Shown on the package cards." />
                <x-admin.textarea name="full_content" label="Full content" :value="$package->full_content" rows="8" maxlength="20000"
                                  help="Markdown supported: **bold**, *italic*, lists (- item), links [text](https://…), ## headings. HTML is removed for safety." />
                <x-admin.textarea name="features_text" label="What's included (one per line)" :value="implode(PHP_EOL, $package->features ?? [])" rows="5"
                                  help="Up to 10 lines. The first 4 appear on package cards." />
            </x-admin.panel>

            <x-admin.panel title="SEO" description="Overrides for the browser title and search snippet.">
                <x-admin.input name="meta_title" label="Meta title" :value="$package->meta_title" maxlength="120" :help="'Default: '.($package->name ?: 'Package name').' Press Release Distribution | VM Newswire'" />
                <x-admin.textarea name="meta_description" label="Meta description" :value="$package->meta_description" rows="2" maxlength="320" help="Default: the short description." />
            </x-admin.panel>
        </div>

        <div class="flex flex-col gap-6 xl:sticky xl:top-24">
            <x-admin.panel title="Visibility">
                <x-admin.toggle name="is_active" label="Active" help="Shown on the website" :checked="$package->is_active" />
                <x-admin.toggle name="is_highlighted" label="Most popular" help="Featured treatment (one package)" :checked="$package->is_highlighted" />
                <x-admin.input name="display_order" label="Display order" type="number" min="0" :value="$package->display_order" help="Lower numbers appear first." />
            </x-admin.panel>
            <x-admin.panel title="Pricing">
                <div class="grid grid-cols-[1fr_110px] gap-3">
                    <x-admin.input name="price" label="Price per release" type="number" step="0.01" min="0" :value="$package->price" help="Empty = “Price on request”" />
                    <x-admin.select name="currency" label="Currency" :value="$package->currency" :options="array_combine(\App\Http\Requests\Admin\PackageRequest::CURRENCIES, \App\Http\Requests\Admin\PackageRequest::CURRENCIES)" />
                </div>
            </x-admin.panel>
        </div>
    </form>

    @if ($editing)
        <div class="grid items-start gap-6 xl:grid-cols-2">
            <x-admin.panel title="Featured media platforms" :description="$featured->count().' of '.\App\Http\Controllers\Admin\PackageMediaController::MAX_FEATURED.' · shown prominently on the package page and as chips on cards'">
                @include('admin.packages.partials.media-list', ['items' => $featured, 'isFeatured' => true])
            </x-admin.panel>

            <x-admin.panel title="Extended network" :description="$network->count().' outlets · listed under “View full distribution network”'">
                @include('admin.packages.partials.media-list', ['items' => $network, 'isFeatured' => false])
                <p class="text-[12px] text-muted">For large lists, use <a href="{{ route('admin.media.import') }}" class="admin-link">CSV import</a> and attach the rows to this package.</p>
            </x-admin.panel>
        </div>

        <x-admin.panel title="Add media outlets" x-data="{ q: '' }">
            @if ($available->isEmpty())
                <p class="text-[13px] text-muted">Every active outlet is already in this package. <a href="{{ route('admin.media.create') }}" class="admin-link">Create a new outlet</a>.</p>
            @else
                <form method="POST" action="{{ route('admin.packages.media.store', $package) }}" class="flex flex-col gap-4">
                    @csrf
                    <div class="relative">
                        <x-icon name="search" :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-soft" />
                        <input type="search" x-model="q" placeholder="Filter {{ $available->count() }} available outlets" aria-label="Filter outlets" class="admin-input pl-9">
                    </div>
                    <div class="grid max-h-72 gap-x-6 overflow-y-auto rounded-[10px] border border-line p-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($available as $outlet)
                            <label x-show="! q || @js(mb_strtolower($outlet->name)).includes(q.toLowerCase())" class="flex items-center gap-2.5 py-1.5 text-[14px]">
                                <input type="checkbox" name="media_ids[]" value="{{ $outlet->id }}" class="size-4 accent-navy-900">
                                <span class="grow">{{ $outlet->name }}</span><span class="text-[12px] text-muted">{{ $outlet->category }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <x-admin.select name="category" label="…or add every active outlet in a category" placeholder="—" :options="array_combine(config('vmnewswire.media_categories'), config('vmnewswire.media_categories'))" class="md:w-80" />
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 text-[13px]"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" class="size-4 accent-navy-900"> Add as featured</label>
                            <button type="submit" class="btn btn-dark btn-sm">Add selected</button>
                        </div>
                    </div>
                </form>
            @endif
        </x-admin.panel>

        <x-admin.panel title="Sample report" description="The PDF visitors can preview and download from the package page.">
            @if ($package->currentReport)
                <div class="flex flex-col gap-3 rounded-[10px] border border-line p-4 sm:flex-row sm:items-center">
                    <span class="flex h-12 w-10 shrink-0 items-center justify-center rounded-[6px] bg-teal-soft text-accent-ink"><x-icon name="file" :size="20" /></span>
                    <div class="flex grow flex-col">
                        <span class="text-[14px] font-semibold text-heading">{{ $package->currentReport->file_name }}</span>
                        <span class="text-[12px] text-muted">{{ $package->currentReport->formatted_size }} · uploaded {{ $package->currentReport->uploaded_at->format('j M Y') }}</span>
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route('admin.sample-reports.download', $package->currentReport) }}" class="admin-link text-heading">Preview</a>
                        <x-admin.confirm-form :action="route('admin.sample-reports.destroy', $package->currentReport)" method="DELETE" confirm="Remove this sample report?">
                            <button type="submit" class="admin-link-danger">Remove</button>
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
                    <input id="report-file" type="file" name="file" accept="application/pdf,.pdf" required class="admin-input">
                    <p class="admin-help">PDF only, up to {{ round(config('vmnewswire.sample_reports.max_kb') / 1024) }} MB.</p>
                    @error('file')<p class="admin-help text-danger">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-dark btn-sm"><x-icon name="upload" :size="15" />Upload</button>
            </form>
        </x-admin.panel>

        <x-admin.panel title="Archive package" description="Removes it from the website. Existing enquiries keep their package and price snapshot. You can restore it later." class="border-danger/30">
            <x-admin.confirm-form :action="route('admin.packages.destroy', $package)" method="DELETE" confirm="Archive this package? It will disappear from the website.">
                <button type="submit" class="btn btn-sm border border-danger/40 bg-white text-danger hover:bg-danger-soft">Archive package</button>
            </x-admin.confirm-form>
        </x-admin.panel>
    @endif

    <x-admin.form-actions>
        <x-slot:note>{{ $editing ? 'Media outlets and the sample report save on their own buttons above.' : 'You can attach media outlets and a sample report after creating the package.' }}</x-slot:note>
        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
        <button type="submit" form="package-form" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />{{ $editing ? 'Save changes' : 'Create package' }}</button>
    </x-admin.form-actions>
</x-layouts.admin>
