@props([
    'href' => null,
    'variant' => 'primary', // primary | secondary | dark | on-brand
    'size' => 'md',         // md | sm
    'icon' => null,
    'iconLeft' => null,
    'type' => 'button',
])
@php
    $classes = 'btn btn-'.$variant.($size === 'sm' ? ' btn-sm' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($iconLeft)<x-icon :name="$iconLeft" :size="17" />@endif
        {{ $slot }}
        @if ($icon)<x-icon :name="$icon" :size="17" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($iconLeft)<x-icon :name="$iconLeft" :size="17" />@endif
        {{ $slot }}
        @if ($icon)<x-icon :name="$icon" :size="17" />@endif
    </button>
@endif
