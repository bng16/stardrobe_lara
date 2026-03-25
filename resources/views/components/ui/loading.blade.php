@props([
    'variant' => 'spinner',
    'size' => 'md',
    'color' => 'primary',
    'text' => null,
    'inline' => false,
    'show' => true
])

@php
// Don't render if show is false
if (!$show) {
    return;
}

// Base classes for loading container
$containerClasses = $inline ? 'inline-flex' : 'flex';
$containerClasses .= ' items-center justify-center gap-2';

// Size classes for different variants
$sizeClasses = [
    'sm' => [
        'spinner' => 'h-4 w-4',
        'dots' => 'h-2 w-2',
        'bars' => 'h-3 w-1'
    ],
    'md' => [
        'spinner' => 'h-6 w-6',
        'dots' => 'h-3 w-3',
        'bars' => 'h-4 w-1.5'
    ],
    'lg' => [
        'spinner' => 'h-8 w-8',
        'dots' => 'h-4 w-4',
        'bars' => 'h-6 w-2'
    ],
    'xl' => [
        'spinner' => 'h-12 w-12',
        'dots' => 'h-6 w-6',
        'bars' => 'h-8 w-3'
    ]
];

// Color classes
$colorClasses = match($color) {
    'secondary' => 'text-gray-600',
    'white' => 'text-white',
    'success' => 'text-green-600',
    'warning' => 'text-yellow-600',
    'danger' => 'text-red-600',
    default => 'text-blue-600'
};

// Text size classes
$textSizeClasses = match($size) {
    'sm' => 'text-sm',
    'lg' => 'text-lg',
    'xl' => 'text-xl',
    default => 'text-base'
};

// Get size class for current variant
$variantSizeClass = $sizeClasses[$size][$variant] ?? $sizeClasses['md'][$variant];

// Combine classes
$classes = trim($containerClasses . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class', 'variant', 'size', 'color', 'text', 'inline', 'show']);
@endphp

<div class="{{ $classes }}" 
     role="status" 
     aria-live="polite"
     aria-label="{{ $text ? 'Loading: ' . $text : 'Loading' }}"
     {{ $filteredAttributes }}>
    
    @if($variant === 'spinner')
        {{-- Spinning circle loader --}}
        <svg class="{{ $variantSizeClass }} {{ $colorClasses }} animate-spin" 
             fill="none" 
             viewBox="0 0 24 24"
             aria-hidden="true">
            <circle class="opacity-25" 
                    cx="12" 
                    cy="12" 
                    r="10" 
                    stroke="currentColor" 
                    stroke-width="4"></circle>
            <path class="opacity-75" 
                  fill="currentColor" 
                  d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($variant === 'dots')
        {{-- Three bouncing dots --}}
        <div class="flex space-x-1" aria-hidden="true">
            <div class="{{ $variantSizeClass }} {{ $colorClasses }} bg-current rounded-full animate-bounce" style="animation-delay: -0.3s"></div>
            <div class="{{ $variantSizeClass }} {{ $colorClasses }} bg-current rounded-full animate-bounce" style="animation-delay: -0.15s"></div>
            <div class="{{ $variantSizeClass }} {{ $colorClasses }} bg-current rounded-full animate-bounce"></div>
        </div>
    @elseif($variant === 'bars')
        {{-- Three scaling bars --}}
        <div class="flex items-end space-x-1" aria-hidden="true">
            <div class="{{ $variantSizeClass }} {{ $colorClasses }} bg-current animate-pulse" style="animation-delay: -0.4s; animation-duration: 1.2s"></div>
            <div class="{{ $variantSizeClass }} {{ $colorClasses }} bg-current animate-pulse" style="animation-delay: -0.2s; animation-duration: 1.2s"></div>
            <div class="{{ $variantSizeClass }} {{ $colorClasses }} bg-current animate-pulse" style="animation-duration: 1.2s"></div>
        </div>
    @endif
    
    @if($text)
        <span class="{{ $textSizeClasses }} {{ $colorClasses }} font-medium">
            {{ $text }}
        </span>
    @endif
    
    {{-- Screen reader only text --}}
    <span class="sr-only">
        {{ $text ?? 'Loading' }}
    </span>
</div>