@props([
    'for' => '',
    'required' => false
])

@php
// Base classes for label
$baseClasses = 'block text-sm font-medium text-gray-700 mb-1';

// Combine base classes with any additional classes
$classes = trim($baseClasses . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class', 'for', 'required']);
@endphp

<label @if($for) for="{{ $for }}" @endif class="{{ $classes }}" {{ $filteredAttributes }}>
    {{ $slot }}
    @if($required)
        <span class="text-red-600" aria-label="required">*</span>
    @endif
</label>
