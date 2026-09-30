<x-layouts.admin title="Upload sample report" breadcrumb="Admin / Sample reports / Upload">
    <form method="POST" action="{{ route('admin.sample-reports.store') }}" enctype="multipart/form-data" class="admin-panel flex max-w-2xl flex-col gap-5">
        @csrf
        <x-admin.select name="package_id" label="Package" placeholder="Choose a package" :value="$selected" :options="$packages->pluck('name', 'id')->all()" required />
        <div>
            <label for="file" class="admin-label">PDF file</label>
            <input id="file" type="file" name="file" accept="application/pdf,.pdf" required class="admin-input py-2 file:mr-3 file:rounded file:border-0 file:bg-[#EFEDF6] file:px-3 file:py-1 file:text-sm">
            <p class="mt-1.5 text-xs text-muted">PDF only, up to {{ round(config('vmnewswire.sample_reports.max_kb') / 1024) }} MB. Uploading replaces the package's current report.</p>
            @error('file')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="upload" :size="16" />Upload</button>
            <a href="{{ route('admin.sample-reports.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
