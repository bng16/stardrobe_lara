@props([
    'href' => null,
    'disabled' => false,
    'destructive' => false,
    'icon' => null,
    'shortcut' => null,
    'noClose' => false
])

@php
// Base classes for dropdown items
$baseClasses = 'flex items-center w-full px-3 py-2 text-sm text-left transition-colors duration-150 ease-in-out';

// State classes
$stateClasses = $disabled 
    ? 'text-gray-400 cursor-not-allowed' 
    : ($destructive 
        ? 'text-red-600 hover:bg-red-50 hover:text-red-700 focus:bg-red-50 focus:text-red-700' 
        : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:bg-gray-100 focus:text-gray-900');

// Focus classes
$focusClasses = $disabled ? '' : 'focus:outline-none';

// Combine all classes
$classes = trim($baseClasses . ' ' . $stateClasses . ' ' . $focusClasses . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes
$filteredAttributes = $attributes->except(['class', 'href', 'disabled', 'destructive', 'icon', 'shortcut', 'noClose']);

// Add role and tabindex
$filteredAttributes = $filteredAttributes->merge([
    'role' => 'menuitem',
    'tabindex' => $disabled ? '-1' : '0'
]);

// Add data attribute to prevent closing if specified
if ($noClose) {
    $filteredAttributes = $filteredAttributes->merge(['data-no-close' => 'true']);
}

// Add disabled attribute if disabled
if ($disabled) {
    $filteredAttributes = $filteredAttributes->merge(['disabled' => true, 'aria-disabled' => 'true']);
}
@endphp

@if($href && !$disabled)
    {{-- Render as link --}}
    <a href="{{ $href }}" 
       class="{{ $classes }}"
       {{ $filteredAttributes }}>
        @if($icon)
            <span class="mr-3 flex-shrink-0 w-4 h-4">
                {!! $icon !!}
            </span>
        @endif
        
        <span class="flex-1">{{ $slot }}</span>
        
        @if($shortcut)
            <span class="ml-auto text-xs text-gray-400 font-mono">{{ $shortcut }}</span>
        @endif
    </a>
@else
    {{-- Render as button --}}
    <button type="button"
            class="{{ $classes }}"
            @if($disabled) disabled @endif
            {{ $filteredAttributes }}>
        @if($icon)
            <span class="mr-3 flex-shrink-0 w-4 h-4">
                {!! $icon !!}
            </span>
        @endif
        
        <span class="flex-1">{{ $slot }}</span>
        
        @if($shortcut)
            <span class="ml-auto text-xs text-gray-400 font-mono">{{ $shortcut }}</span>
        @endif
    </button>
@endif