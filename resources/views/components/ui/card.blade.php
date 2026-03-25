@props([
    'class' => ''
])

@php
// Base classes for card container
$baseClasses = 'rounded-lg border bg-card text-card-foreground shadow-sm';

// Combine base classes with any additional classes
$classes = trim($baseClasses . ' ' . $class . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class']);
@endphp

<div class="{{ $classes }}" {{ $filteredAttributes }}>
    {{ $slot }}
</div>