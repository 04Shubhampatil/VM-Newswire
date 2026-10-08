<x-layouts.admin title="Import media outlets" description="Upload a CSV to add many outlets at once. Every row is validated; invalid rows are reported and skipped." :breadcrumbs="[['Media Network', route('admin.media.index')], ['Import', null]]">
    <x-slot:actions>
        <x-button :href="route('admin.media.import.template')" variant="secondary" size="sm" icon-left="download">Download template</x-button>
    </x-slot:actions>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <x-admin.panel title="Upload CSV">
            <form method="POST" action="{{ route('admin.media.import.store') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
                @csrf
                <div class="flex flex-col items-center gap-3 rounded-[12px] border-[1.5px] border-dashed border-line bg-canvas px-6 py-10 text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-teal-soft text-accent-ink"><x-icon name="upload" :size="22" /></span>
                    <label for="csv" class="text-[15px] font-semibold text-heading">Choose a CSV file</label>
                    <input id="csv" type="file" name="file" accept=".csv,text/csv" required class="text-[13px] file:mr-3 file:rounded-[6px] file:border-0 file:bg-white file:px-3 file:py-1.5 file:text-[13px] file:font-medium">
                    <p class="text-[12px] text-muted">Columns: <code class="font-mono">name, website_url, logo_url, category, is_active</code> · max 2 MB / 5,000 rows</p>
                    @error('file')<p class="text-[13px] text-danger">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2.5 text-[14px]"><input type="hidden" name="update_existing" value="0"><input type="checkbox" name="update_existing" value="1" class="size-4 accent-navy-900"> Update existing outlets with the same name</label>
                <x-admin.select name="package_id" label="Also add imported outlets to a package's network (optional)" placeholder="Don't attach" :options="$packages->pluck('name', 'id')->all()" />
                <button type="submit" class="btn btn-primary btn-sm self-start"><x-icon name="upload" :size="15" />Import CSV</button>
            </form>
        </x-admin.panel>

        <x-admin.panel title="Rules">
            <ul class="flex flex-col gap-2 text-[13px] leading-relaxed text-muted">
                <li class="flex gap-2"><x-icon name="check" :size="15" :stroke="2.4" class="mt-0.5 shrink-0 text-accent-ink" /><span><strong class="text-heading">name</strong> and <strong class="text-heading">category</strong> are required.</span></li>
                <li class="flex gap-2"><x-icon name="check" :size="15" :stroke="2.4" class="mt-0.5 shrink-0 text-accent-ink" /><span>Category must be one of: {{ implode(', ', config('vmnewswire.media_categories')) }}.</span></li>
                <li class="flex gap-2"><x-icon name="check" :size="15" :stroke="2.4" class="mt-0.5 shrink-0 text-accent-ink" /><span>URLs must start with http(s); logo URLs must use https.</span></li>
                <li class="flex gap-2"><x-icon name="check" :size="15" :stroke="2.4" class="mt-0.5 shrink-0 text-accent-ink" /><span><strong class="text-heading">is_active</strong>: 1/0, true/false or yes/no (default: active).</span></li>
            </ul>
        </x-admin.panel>
    </div>

    @if ($result)
        <x-admin.panel title="Import results" flush>
            <div class="grid grid-cols-2 gap-4 p-6 md:grid-cols-4">
                <x-admin.metric-card label="Imported" :value="$result['imported']" />
                <x-admin.metric-card label="Updated" :value="$result['updated']" />
                <x-admin.metric-card label="Skipped" :value="$result['skipped']" />
                <x-admin.metric-card label="Failed" :value="$result['failed']" :accent="$result['failed'] > 0" />
            </div>
            @if ($result['errors'])
                <x-admin.data-table caption="Rows skipped or failed" :min-width="640" class="rounded-none border-x-0 border-b-0 shadow-none">
                    <x-slot:head><th class="w-20">Row</th><th>Name</th><th>Reason</th></x-slot:head>
                    @foreach (array_slice($result['errors'], 0, 300) as $error)
                        <tr><td class="font-mono text-[12px]">{{ $error['row'] }}</td><td>{{ $error['name'] ?: '—' }}</td><td class="text-muted">{{ $error['message'] }}</td></tr>
                    @endforeach
                    @if (count($result['errors']) > 300)
                        <x-slot:footer><span class="text-muted">Showing the first 300 of {{ count($result['errors']) }} issues.</span></x-slot:footer>
                    @endif
                </x-admin.data-table>
            @endif
        </x-admin.panel>
    @endif
</x-layouts.admin>
