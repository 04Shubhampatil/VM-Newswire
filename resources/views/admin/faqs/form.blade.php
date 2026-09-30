@php $editing = $faq->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit FAQ' : 'New FAQ'" breadcrumb="Admin / Website content / FAQ">
    <form method="POST" action="{{ $editing ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="admin-panel flex max-w-3xl flex-col gap-5">
        @csrf
        @if ($editing) @method('PUT') @endif
        <x-admin.input name="question" label="Question" :value="$faq->question" required maxlength="255" />
        <x-admin.textarea name="answer" label="Answer" :value="$faq->answer" rows="5" required maxlength="3000" />
        <div class="grid gap-5 md:grid-cols-3">
            <x-admin.select name="category" label="Category" :value="$faq->category" :options="array_combine(\App\Models\Faq::CATEGORIES, \App\Models\Faq::CATEGORIES)" />
            <x-admin.select name="package_id" label="Show on" placeholder="FAQ page (general)" :value="$faq->package_id" :options="$packages->pluck('name', 'id')->all()" />
            <x-admin.input name="display_order" label="Order" type="number" min="0" :value="$faq->display_order" />
        </div>
        <x-admin.toggle name="is_active" label="Published" :checked="$faq->is_active" />
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary btn-sm">{{ $editing ? 'Save FAQ' : 'Add FAQ' }}</button>
            <a href="{{ route('admin.content.edit') }}#faqs" class="btn btn-secondary btn-sm">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
