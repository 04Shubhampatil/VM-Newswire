<x-layouts.admin title="Upload sample report" description="Uploading replaces the package's current report." :breadcrumbs="[['Sample Reports', route('admin.sample-reports.index')], ['Upload', null]]">
    <form method="POST" action="{{ route('admin.sample-reports.store') }}" enctype="multipart/form-data" class="flex max-w-2xl flex-col gap-6">
        @csrf
        <x-admin.panel title="Report">
            <x-admin.select name="package_id" label="Package" placeholder="Choose a package" :value="$selected" :options="$packages->pluck('name', 'id')->all()" required />
            <div>
                <label for="file" class="admin-label">PDF file</label>
                <input id="file" type="file" name="file" accept="application/pdf,.pdf" required class="admin-input">
                <p class="admin-help">PDF only, up to {{ round(config('vmnewswire.sample_reports.max_kb') / 1024) }} MB.</p>
                @error('file')<p class="admin-help text-danger">{{ $message }}</p>@enderror
            </div>
        </x-admin.panel>
        <div class="flex gap-2">
            <a href="{{ route('admin.sample-reports.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="upload" :size="15" />Upload</button>
        </div>
    </form>
</x-layouts.admin>
