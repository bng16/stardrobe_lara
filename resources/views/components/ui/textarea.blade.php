@props([
    'name' => '',
    'id' => '',
    'value' => '',
    'placeholder' => '',
    'disabled' => false,
    'required' => false,
    'readonly' => false,
    'rows' => 3,
    'error' => null
])

@php
// Generate ID from name if not provided
$inputId = $id ?: $name;

// Get old value for form repopulation
$inputValue = $name ? old($name, $value) : $value;

// Base classes for textarea
$baseClasses = 'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

// Error state classes
$errorClasses = $error ? 'border-destructive focus-visible:ring-destructive' : '';

// Combine all classes
$classes = trim($baseClasses . ' ' . $errorClasses . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class', 'name', 'id', 'value', 'placeholder', 'disabled', 'required', 'readonly', 'rows', 'error']);
@endphp

<textarea name="{{ $name }}"
          @if($inputId) id="{{ $inputId }}" @endif
          @if($placeholder) placeholder="{{ $placeholder }}" @endif
          @if($disabled) disabled @endif
          @if($required) required @endif
          @if($readonly) readonly @endif
          rows="{{ $rows }}"
          class="{{ $classes }}"
          {{ $filteredAttributes }}>{{ $inputValue }}</textarea>