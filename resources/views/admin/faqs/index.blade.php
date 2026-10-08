@php $grouped = $faqs->groupBy(fn ($faq) => $faq->package?->name ?? 'FAQ page'); @endphp
<x-layouts.admin title="FAQs" description="Questions shown on the FAQ page and on individual package pages." :breadcrumbs="[['FAQs', null]]">
    <x-slot:actions>
        <x-button :href="route('faq')" variant="secondary" size="sm" target="_blank" icon-left="external">View FAQ page</x-button>
        <x-button :href="route('admin.faqs.create')" size="sm" icon-left="plus">Add FAQ</x-button>
    </x-slot:actions>

    <div class="flex flex-wrap gap-2">
        <span class="admin-chip" aria-current="page">All <span class="admin-chip-count">{{ $faqs->count() }}</span></span>
        <span class="admin-chip">Published <span class="admin-chip-count">{{ $faqs->where('is_active', true)->count() }}</span></span>
        <span class="admin-chip">Hidden <span class="admin-chip-count">{{ $faqs->where('is_active', false)->count() }}</span></span>
    </div>

    @if ($faqs->isEmpty())
        <x-admin.panel flush>
            <x-admin.empty-state icon="info" title="No FAQs yet" text="Add the questions customers ask most; they appear on the FAQ page and can be attached to a package.">
                <x-button :href="route('admin.faqs.create')" size="sm" icon-left="plus">Add the first FAQ</x-button>
            </x-admin.empty-state>
        </x-admin.panel>
    @else
        @foreach ($grouped as $where => $items)
            <x-admin.panel :title="$where" :description="$items->count().' question(s) · ordered by the “Order” field'" flush>
                <x-admin.data-table :caption="'FAQs shown on '.$where" :min-width="640" class="rounded-none border-0 shadow-none">
                    <x-slot:head><th class="w-16">Order</th><th>Question</th><th class="w-40">Category</th><th class="w-32">Status</th><th class="w-40 text-right">Actions</th></x-slot:head>
                    @foreach ($items as $faq)
                        <tr>
                            <td class="font-mono text-[12px] text-muted">{{ $faq->display_order }}</td>
                            <td>
                                <a href="{{ route('admin.faqs.edit', $faq) }}" class="font-semibold text-heading hover:text-accent-ink">{{ $faq->question }}</a>
                                <p class="mt-0.5 line-clamp-1 max-w-xl text-[12px] text-muted">{{ $faq->answer }}</p>
                            </td>
                            <td class="text-muted">{{ $faq->category }}</td>
                            <td><x-admin.status-badge :status="$faq->is_active ? 'active' : 'inactive'" /></td>
                            <td>
                                <div class="flex justify-end gap-4">
                                    <a href="{{ route('admin.faqs.edit', $faq) }}" class="admin-link">Edit</a>
                                    <x-admin.confirm-form :action="route('admin.faqs.destroy', $faq)" method="DELETE" confirm="Delete this FAQ?">
                                        <button type="submit" class="admin-link-danger">Delete</button>
                                    </x-admin.confirm-form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-admin.data-table>
            </x-admin.panel>
        @endforeach
    @endif
</x-layouts.admin>
