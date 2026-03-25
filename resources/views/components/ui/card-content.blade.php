@props([
    'class' => ''
])

@php
// Base classes for card content
$baseClasses = 'p-6 pt-0';

// Combine base classes with any additional classes
$classes = trim($baseClasses . ' ' . $class . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class']);
@endphp

<div class="{{ $classes }}" {{ $filteredAttributes }}>
    {{ $slot }}
</div>