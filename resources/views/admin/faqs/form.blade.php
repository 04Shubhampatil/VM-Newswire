@php $editing = $faq->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit FAQ' : 'New FAQ'" :breadcrumbs="[['FAQs', route('admin.faqs.index')], [$editing ? 'Edit' : 'New', null]]">
    <form method="POST" action="{{ $editing ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="flex flex-col gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <x-admin.panel title="Question and answer">
                <x-admin.input name="question" label="Question" :value="$faq->question" required maxlength="255" />
                <x-admin.textarea name="answer" label="Answer" :value="$faq->answer" rows="7" required maxlength="3000" help="Plain text. Line breaks are kept." />
            </x-admin.panel>

            <x-admin.panel title="Placement">
                <x-admin.select name="category" label="Category" :value="$faq->category" :options="array_combine(\App\Models\Faq::CATEGORIES, \App\Models\Faq::CATEGORIES)" />
                <x-admin.select name="package_id" label="Show on" placeholder="FAQ page (general)" :value="$faq->package_id" :options="$packages->pluck('name', 'id')->all()" help="Attach to a package to show it on that package's page instead." />
                <x-admin.input name="display_order" label="Order" type="number" min="0" :value="$faq->display_order" help="Lower numbers appear first." />
                <x-admin.toggle name="is_active" label="Published" help="Visible on the website" :checked="$faq->is_active" />
            </x-admin.panel>
        </div>

        <x-admin.form-actions>
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="15" />{{ $editing ? 'Save FAQ' : 'Add FAQ' }}</button>
        </x-admin.form-actions>
    </form>
</x-layouts.admin>
