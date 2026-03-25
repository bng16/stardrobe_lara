@props([
    'name' => '',
    'id' => '',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'required' => false
])

@php
// Generate ID from name if not provided
$inputId = $id ?: $name;

// Check if checkbox should be checked (from old input or prop)
$isChecked = $name ? old($name, $checked) : $checked;

// Base classes for checkbox
$baseClasses = 'h-4 w-4 rounded border-gray-300 text-primary focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

// Combine all classes
$classes = trim($baseClasses . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class', 'name', 'id', 'value', 'checked', 'disabled', 'required']);
@endphp

<input type="checkbox"
       name="{{ $name }}"
       @if($inputId) id="{{ $inputId }}" @endif
       value="{{ $value }}"
       @if($isChecked) checked @endif
       @if($disabled) disabled @endif
       @if($required) required @endif
       class="{{ $classes }}"
       {{ $filteredAttributes }}>
