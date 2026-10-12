<x-layouts.admin title="Blogs" description="Blog posts shown on the public Blogs page." :breadcrumbs="[['Blogs', null]]">
    <x-slot:actions>
        <x-button :href="route('blogs.index')" variant="secondary" size="sm" target="_blank" icon-left="external">View Blogs page</x-button>
        <x-button :href="route('admin.blogs.create')" size="sm" icon-left="plus">Add blog post</x-button>
    </x-slot:actions>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.blogs.index') }}" class="admin-chip" @if (! $status) aria-current="page" @endif>All <span class="admin-chip-count">{{ $counts['all'] }}</span></a>
        <a href="{{ route('admin.blogs.index', ['status' => 'published']) }}" class="admin-chip" @if ($status === 'published') aria-current="page" @endif>Published <span class="admin-chip-count">{{ $counts['published'] }}</span></a>
        <a href="{{ route('admin.blogs.index', ['status' => 'draft']) }}" class="admin-chip" @if ($status === 'draft') aria-current="page" @endif>Drafts <span class="admin-chip-count">{{ $counts['draft'] }}</span></a>
    </div>

    @if ($releases->isEmpty())
        <x-admin.panel flush>
            <x-admin.empty-state icon="file" title="No blog posts yet" text="Add a blog post and it appears on the public Blogs page straight away.">
                <x-button :href="route('admin.blogs.create')" size="sm" icon-left="plus">Add the first blog post</x-button>
            </x-admin.empty-state>
        </x-admin.panel>
    @else
        <x-admin.panel title="Blog posts" :description="$releases->total().' in total · newest first'" flush>
            <x-admin.data-table caption="Blog posts" :min-width="760" class="rounded-none border-0 shadow-none">
                <x-slot:head><th>Title</th><th class="w-36">Category</th><th class="w-32">Published</th><th class="w-28">Status</th><th class="w-40 text-right">Actions</th></x-slot:head>
                @foreach ($releases as $release)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-[6px] border border-line bg-canvas-deep">
                                    @if ($release->image_url)<img src="{{ $release->image_url }}" alt="" class="size-full object-cover">@else<x-icon name="file" :size="16" class="text-muted-soft" />@endif
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.blogs.edit', $release) }}" class="font-semibold text-heading hover:text-accent-ink">{{ $release->title }}</a>
                                    <p class="mt-0.5 line-clamp-1 max-w-xl text-[12px] text-muted">By {{ $release->author_name }} · /blogs/{{ $release->slug }}</p>
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
                                    <a href="{{ route('blogs.show', $release->slug) }}" target="_blank" rel="noopener" class="admin-link">View</a>
                                @endif
                                <a href="{{ route('admin.blogs.edit', $release) }}" class="admin-link">Edit</a>
                                <x-admin.confirm-form :action="route('admin.blogs.destroy', $release)" method="DELETE" confirm="Delete this blog post?">
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
