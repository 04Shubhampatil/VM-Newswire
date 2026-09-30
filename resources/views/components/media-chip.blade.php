@props(['name'])
<span {{ $attributes->merge(['class' => 'chip']) }}>{{ $name }}</span>
