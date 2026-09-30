@props(['name', 'label', 'value' => null, 'type' => 'text', 'help' => null, 'mono' => false])
@php $id = 'f-'.str_replace(['[', ']', '.'], '-', $name); $errorKey = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="admin-label">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($errorKey, $value) }}"
           {{ $attributes->except('class')->merge(['class' => 'admin-input'.($mono ? ' font-mono text-sm' : '').($errors->has($errorKey) ? ' border-danger' : '')]) }}
           @if ($help) aria-describedby="{{ $id }}-help" @endif @error($errorKey) aria-invalid="true" @enderror>
    @if ($help)<p id="{{ $id }}-help" class="mt-1.5 text-xs text-muted">{{ $help }}</p>@endif
    @error($errorKey)<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
</div>
