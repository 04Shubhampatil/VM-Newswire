@php
    $message = session('toast');
    $error = session('toast_error');
@endphp
@if ($message || $error)
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity
         role="status" aria-live="polite"
         class="fixed right-5 bottom-5 z-50 flex max-w-md items-start gap-3 rounded-[8px] px-5 py-4 text-sm text-white shadow-lg {{ $error ? 'bg-danger' : 'bg-brand' }}">
        <x-icon :name="$error ? 'x' : 'check'" :size="18" class="{{ $error ? '' : 'text-accent-on-brand' }} mt-px" />
        <span class="grow">{{ $error ?: $message }}</span>
        <button type="button" @click="show = false" aria-label="Dismiss" class="-m-1 p-1 opacity-70 hover:opacity-100"><x-icon name="x" :size="16" /></button>
    </div>
@endif
