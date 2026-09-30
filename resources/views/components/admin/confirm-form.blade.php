@props(['action', 'method' => 'POST', 'confirm' => 'Are you sure?'])
{{-- Destructive / state-changing actions always use a non-GET form with a confirmation prompt. --}}
<form method="POST" action="{{ $action }}" x-data @submit="if (! confirm(@js($confirm))) $event.preventDefault()" {{ $attributes->only('class') }}>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif
    {{ $slot }}
</form>
