@props([
    'class' => ''
])

@php
// Base classes for card header
$baseClasses = 'flex flex-col space-y-1.5 p-6';

// Combine base classes with any additional classes
$classes = trim($baseClasses . ' ' . $class . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class']);
@endphp

<div class="{{ $classes }}" {{ $filteredAttributes }}>
    {{ $slot }}
</div>