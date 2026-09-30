<x-layouts.admin title="Bulk import media outlets" breadcrumb="Admin / Media outlets / Import">
    <x-slot:actions>
        <x-button :href="route('admin.media.import.template')" variant="secondary" size="sm" icon-left="download">Download template</x-button>
    </x-slot:actions>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <form method="POST" action="{{ route('admin.media.import.store') }}" enctype="multipart/form-data" class="admin-panel flex flex-col gap-5">
            @csrf
            <div class="flex flex-col items-center gap-3 rounded-[8px] border-[1.5px] border-dashed border-[#b9b3a5] bg-canvas px-6 py-8 text-center">
                <x-icon name="upload" :size="26" class="text-accent-ink" />
                <label for="csv" class="text-[15px] font-semibold">Choose a CSV file</label>
                <input id="csv" type="file" name="file" accept=".csv,text/csv" required class="text-sm file:mr-3 file:rounded file:border-0 file:bg-white file:px-3 file:py-1.5 file:text-sm">
                <p class="text-xs text-muted">Columns: <code class="font-mono">name, website_url, logo_url, category, is_active</code> · max 2 MB / 5,000 rows</p>
                @error('file')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2.5 text-sm"><input type="hidden" name="update_existing" value="0"><input type="checkbox" name="update_existing" value="1" class="size-4 accent-accent-fill"> Update existing outlets with the same name</label>
            <x-admin.select name="package_id" label="Also add imported outlets to a package's network (optional)" placeholder="Don't attach" :options="$packages->pluck('name', 'id')->all()" />
            <button type="submit" class="btn btn-primary btn-sm self-start">Import CSV</button>
        </form>

        <aside class="admin-panel flex flex-col gap-3 text-sm">
            <h2 class="font-semibold">Rules</h2>
            <ul class="list-disc space-y-1.5 pl-5 text-muted">
                <li><strong>name</strong> and <strong>category</strong> are required.</li>
                <li>Category must be one of: {{ implode(', ', config('vmnewswire.media_categories')) }}.</li>
                <li>URLs must start with http(s); logo URLs must use https.</li>
                <li><strong>is_active</strong>: 1/0, true/false or yes/no (default: active).</li>
                <li>Every row is validated; invalid rows are reported and skipped.</li>
            </ul>
        </aside>
    </div>

    @if ($result)
        <section class="flex flex-col gap-4">
            <h2 class="text-lg font-semibold">Import results</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <x-admin.metric-card label="Imported" :value="$result['imported']" />
                <x-admin.metric-card label="Updated" :value="$result['updated']" />
                <x-admin.metric-card label="Skipped" :value="$result['skipped']" />
                <x-admin.metric-card label="Failed" :value="$result['failed']" :accent="$result['failed'] > 0" />
            </div>
            @if ($result['errors'])
                <x-admin.data-table caption="Rows skipped or failed">
                    <x-slot:head><th class="w-20">Row</th><th>Name</th><th>Reason</th></x-slot:head>
                    @foreach (array_slice($result['errors'], 0, 300) as $error)
                        <tr><td class="font-mono">{{ $error['row'] }}</td><td>{{ $error['name'] ?: '—' }}</td><td class="text-muted">{{ $error['message'] }}</td></tr>
                    @endforeach
                    @if (count($result['errors']) > 300)
                        <x-slot:footer><span class="text-sm text-muted">Showing the first 300 of {{ count($result['errors']) }} issues.</span></x-slot:footer>
                    @endif
                </x-admin.data-table>
            @endif
        </section>
    @endif
</x-layouts.admin>
