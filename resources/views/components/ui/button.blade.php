@props([
    'variant' => 'default',
    'size' => 'default',
    'type' => 'button',
    'disabled' => false,
    'href' => null,
    'asChild' => false
])

@php
// Base classes for all buttons
$baseClasses = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';

// Variant classes
$variantClasses = match($variant) {
    'destructive' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
    'outline' => 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
    'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
    'ghost' => 'hover:bg-accent hover:text-accent-foreground',
    'link' => 'text-primary underline-offset-4 hover:underline',
    default => 'bg-primary text-primary-foreground hover:bg-primary/90'
};

// Size classes
$sizeClasses = match($size) {
    'sm' => 'h-9 rounded-md px-3',
    'lg' => 'h-11 rounded-md px-8',
    'icon' => 'h-10 w-10',
    default => 'h-10 px-4 py-2'
};

// Combine all classes
$classes = trim($baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class', 'variant', 'size', 'type', 'disabled', 'href', 'asChild']);
@endphp

@if($href)
    {{-- Render as link when href is provided --}}
    <a href="{{ $href }}" 
       class="{{ $classes }}"
       @if($disabled) aria-disabled="true" tabindex="-1" @endif
       {{ $filteredAttributes }}>
        {{ $slot }}
    </a>
@else
    {{-- Render as button --}}
    <button type="{{ $type }}" 
            @if($disabled) disabled @endif
            class="{{ $classes }}"
            {{ $filteredAttributes }}>
        {{ $slot }}
    </button>
@endif