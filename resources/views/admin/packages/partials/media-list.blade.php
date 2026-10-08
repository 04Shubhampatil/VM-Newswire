@if ($items->isEmpty())
    <p class="rounded-[10px] border border-dashed border-line px-4 py-6 text-center text-[13px] text-muted">No {{ $isFeatured ? 'featured platforms' : 'network outlets' }} yet.</p>
@else
    <ul class="flex flex-col divide-y divide-line-soft rounded-[10px] border border-line {{ $isFeatured ? '' : 'max-h-96 overflow-y-auto' }}">
        @foreach ($items as $outlet)
            <li class="flex items-center gap-3 px-4 py-2.5 text-[14px]">
                <span class="grow font-medium text-heading">{{ $outlet->name }} <span class="ml-1 text-[12px] font-normal text-muted">{{ $outlet->category }}</span>
                    @unless ($outlet->is_active)<span class="ml-1 text-[12px] text-danger">(inactive)</span>@endunless
                </span>
                @php $actions = $isFeatured ? ['up' => 'Move up', 'down' => 'Move down', 'unfeature' => 'Move to network'] : ['feature' => 'Make featured']; @endphp
                @foreach ($actions as $action => $label)
                    <form method="POST" action="{{ route('admin.packages.media.update', [$package, $outlet]) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="{{ $action }}">
                        @if (in_array($action, ['up', 'down']))
                            <button type="submit" aria-label="{{ $label }}: {{ $outlet->name }}" class="admin-icon-btn" @disabled(($action === 'up' && $loop->parent->first) || ($action === 'down' && $loop->parent->last))>
                                <x-icon :name="$action === 'up' ? 'arrow-up' : 'arrow-down'" :size="14" />
                            </button>
                        @else
                            <button type="submit" class="admin-link text-[12px] text-heading">{{ $label }}</button>
                        @endif
                    </form>
                @endforeach
                <x-admin.confirm-form :action="route('admin.packages.media.destroy', [$package, $outlet])" method="DELETE" :confirm="'Remove '.$outlet->name.' from this package?'">
                    <button type="submit" aria-label="Remove {{ $outlet->name }}" class="flex size-8 items-center justify-center rounded-[6px] text-muted transition hover:bg-danger-soft hover:text-danger"><x-icon name="x" :size="15" /></button>
                </x-admin.confirm-form>
            </li>
        @endforeach
    </ul>
@endif
