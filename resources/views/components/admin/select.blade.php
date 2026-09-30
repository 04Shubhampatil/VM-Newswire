@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null])
{{-- $options: [value => label] --}}
@php $id = 'f-'.$name; $current = (string) old($name, $value instanceof \BackedEnum ? $value->value : $value); @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="admin-label">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('class')->merge(['class' => 'admin-input'.($errors->has($name) ? ' border-danger' : '')]) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($help)<p class="mt-1.5 text-xs text-muted">{{ $help }}</p>@endif
    @error($name)<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
</div>
