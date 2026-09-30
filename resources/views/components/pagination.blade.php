@props(['paginator'])
@if ($paginator->hasPages())
    <nav aria-label="Pagination" class="flex items-center justify-between gap-4 text-sm">
        <span class="text-muted">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm pointer-events-none opacity-40">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm">Previous</a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm">Next</a>
            @else
                <span class="btn btn-secondary btn-sm pointer-events-none opacity-40">Next</span>
            @endif
        </div>
    </nav>
@endif
