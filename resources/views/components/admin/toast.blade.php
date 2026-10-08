@php
    $message = session('toast');
    $error = session('toast_error');
@endphp
@if ($message || $error)
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity
         role="status" aria-live="polite"
         class="fixed right-5 bottom-5 z-50 flex max-w-md items-start gap-3 rounded-[12px] px-5 py-4 text-[14px] text-white shadow-[var(--shadow-float)] {{ $error ? 'bg-danger' : 'bg-navy-900' }}">
        <span class="flex size-6 shrink-0 items-center justify-center rounded-full {{ $error ? 'bg-white/20' : 'bg-teal text-navy-900' }}"><x-icon :name="$error ? 'x' : 'check'" :size="14" :stroke="3" /></span>
        <span class="grow pt-0.5">{{ $error ?: $message }}</span>
        <button type="button" @click="show = false" aria-label="Dismiss" class="-m-1 p-1 opacity-70 hover:opacity-100"><x-icon name="x" :size="16" /></button>
    </div>
@endif
