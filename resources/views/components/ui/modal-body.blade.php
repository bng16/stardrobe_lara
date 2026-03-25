@props([
    'class' => ''
])

@php
$classes = 'p-6 ' . $class;
@endphp

<div class="{{ $classes }}" {{ $attributes }}>
    {{ $slot }}
</div>