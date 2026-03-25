@props([
    'class' => ''
])

@php
$classes = 'flex items-center justify-end gap-2 p-6 border-t border-border ' . $class;
@endphp

<div class="{{ $classes }}" {{ $attributes }}>
    {{ $slot }}
</div>