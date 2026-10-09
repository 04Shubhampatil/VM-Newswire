<x-layouts.admin title="Newsroom" description="Press releases shown on the public Newsroom page." :breadcrumbs="[['Newsroom', null]]">
    <x-slot:actions>
        <x-button :href="route('newsroom.index')" variant="secondary" size="sm" target="_blank" icon-left="external">View Newsroom</x-button>
        <x-button :href="route('admin.newsroom.create')" size="sm" icon-left="plus">Add press release</x-button>
    </x-slot:actions>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.newsroom.index') }}" class="admin-chip" @if (! $status) aria-current="page" @endif>All <span class="admin-chip-count">{{ $counts['all'] }}</span></a>
        <a href="{{ route('admin.newsroom.index', ['status' => 'published']) }}" class="admin-chip" @if ($status === 'published') aria-current="page" @endif>Published <span class="admin-chip-count">{{ $counts['published'] }}</span></a>
        <a href="{{ route('admin.newsroom.index', ['status' => 'draft']) }}" class="admin-chip" @if ($status === 'draft') aria-current="page" @endif>Drafts <span class="admin-chip-count">{{ $counts['draft'] }}</span></a>
    </div>

    @if ($releases->isEmpty())
        <x-admin.panel flush>
            <x-admin.empty-state icon="file" title="No press releases yet" text="Add a press release and it appears on the public Newsroom page straight away.">
                <x-button :href="route('admin.newsroom.create')" size="sm" icon-left="plus">Add the first press release</x-button>
            </x-admin.empty-state>
        </x-admin.panel>
    @else
        <x-admin.panel title="Press releases" :description="$releases->total().' in total · newest first'" flush>
            <x-admin.data-table caption="Press releases" :min-width="760" class="rounded-none border-0 shadow-none">
                <x-slot:head><th>Title</th><th class="w-36">Category</th><th class="w-32">Published</th><th class="w-28">Status</th><th class="w-40 text-right">Actions</th></x-slot:head>
                @foreach ($releases as $release)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-[6px] border border-line bg-canvas-deep">
                                    @if ($release->image_url)<img src="{{ $release->image_url }}" alt="" class="size-full object-cover">@else<x-icon name="file" :size="16" class="text-muted-soft" />@endif
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.newsroom.edit', $release) }}" class="font-semibold text-heading hover:text-accent-ink">{{ $release->title }}</a>
                                    <p class="mt-0.5 line-clamp-1 max-w-xl text-[12px] text-muted">By {{ $release->author_name }} · /newsroom/{{ $release->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $release->category }}</td>
                        <td class="text-muted">{{ $release->published_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            @php $state = $release->is_published ? ($release->published_at?->isFuture() ? 'pending' : 'active') : 'inactive'; @endphp
                            <x-admin.status-badge :status="$state" />
                            <span class="sr-only">{{ ['pending' => 'Scheduled', 'active' => 'Published', 'inactive' => 'Draft'][$state] }}</span>
                        </td>
                        <td>
                            <div class="flex justify-end gap-4">
                                @if ($release->is_published && $release->published_at?->isPast())
                                    <a href="{{ route('newsroom.show', $release->slug) }}" target="_blank" rel="noopener" class="admin-link">View</a>
                                @endif
                                <a href="{{ route('admin.newsroom.edit', $release) }}" class="admin-link">Edit</a>
                                <x-admin.confirm-form :action="route('admin.newsroom.destroy', $release)" method="DELETE" confirm="Delete this press release?">
                                    <button type="submit" class="admin-link-danger">Delete</button>
                                </x-admin.confirm-form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-admin.data-table>
        </x-admin.panel>
        @if ($releases->hasPages())
            <div class="flex items-center justify-between gap-4 text-[13px] text-muted">
                <span>Showing {{ $releases->firstItem() }}–{{ $releases->lastItem() }} of {{ $releases->total() }}</span>
                <div class="flex gap-2">
                    @if (! $releases->onFirstPage())<a href="{{ $releases->previousPageUrl() }}" class="btn btn-secondary btn-sm">Previous</a>@endif
                    @if ($releases->hasMorePages())<a href="{{ $releases->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next</a>@endif
                </div>
            </div>
        @endif
    @endif
</x-layouts.admin>
