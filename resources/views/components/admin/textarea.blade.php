@props(['name', 'label', 'value' => null, 'rows' => 4, 'help' => null, 'mono' => false])
@php $id = 'f-'.$name; @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="admin-label">{{ $label }}</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
              {{ $attributes->except('class')->merge(['class' => 'admin-input'.($mono ? ' font-mono text-[13px] leading-relaxed' : '').($errors->has($name) ? ' border-danger' : '')]) }}
              @if ($help) aria-describedby="{{ $id }}-help" @endif @error($name) aria-invalid="true" @enderror>{{ old($name, $value) }}</textarea>
    @if ($help)<p id="{{ $id }}-help" class="mt-1.5 text-xs text-muted">{{ $help }}</p>@endif
    @error($name)<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
</div>
