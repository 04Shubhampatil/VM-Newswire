@if ($items->isEmpty())
    <p class="rounded-[6px] border border-dashed border-[#D6D2E6] px-4 py-6 text-center text-sm text-muted">No {{ $isFeatured ? 'featured platforms' : 'network outlets' }} yet.</p>
@else
    <ul class="flex flex-col divide-y divide-[#EFEDF6] rounded-[6px] border border-[#E6E4EF] {{ $isFeatured ? '' : 'max-h-96 overflow-y-auto' }}">
        @foreach ($items as $outlet)
            <li class="flex items-center gap-3 px-4 py-2.5 text-sm">
                <span class="grow font-medium">{{ $outlet->name }} <span class="ml-1 text-xs font-normal text-muted">{{ $outlet->category }}</span>
                    @unless ($outlet->is_active)<span class="ml-1 text-xs text-danger">(inactive)</span>@endunless
                </span>
                @php $actions = $isFeatured ? ['up' => 'Move up', 'down' => 'Move down', 'unfeature' => 'Move to network'] : ['feature' => 'Make featured']; @endphp
                @foreach ($actions as $action => $label)
                    <form method="POST" action="{{ route('admin.packages.media.update', [$package, $outlet]) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="{{ $action }}">
                        @if (in_array($action, ['up', 'down']))
                            <button type="submit" aria-label="{{ $label }}: {{ $outlet->name }}" class="flex size-8 items-center justify-center rounded-[6px] border border-[#E6E4EF] text-muted hover:border-ink hover:text-ink disabled:opacity-30" @disabled(($action === 'up' && $loop->parent->first) || ($action === 'down' && $loop->parent->last))>
                                <x-icon :name="$action === 'up' ? 'arrow-up' : 'arrow-down'" :size="14" />
                            </button>
                        @else
                            <button type="submit" class="text-xs font-semibold text-ink hover:underline">{{ $label }}</button>
                        @endif
                    </form>
                @endforeach
                <x-admin.confirm-form :action="route('admin.packages.media.destroy', [$package, $outlet])" method="DELETE" :confirm="'Remove '.$outlet->name.' from this package?'">
                    <button type="submit" aria-label="Remove {{ $outlet->name }}" class="flex size-8 items-center justify-center text-muted hover:text-danger"><x-icon name="x" :size="15" /></button>
                </x-admin.confirm-form>
            </li>
        @endforeach
    </ul>
@endif
