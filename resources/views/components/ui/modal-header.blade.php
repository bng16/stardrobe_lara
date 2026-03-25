@props([
    'title' => null,
    'description' => null,
    'dismissible' => true,
    'modalId' => null
])

@php
$classes = 'flex items-center justify-between p-6 border-b border-border';
@endphp

<div class="{{ $classes }}" {{ $attributes }}>
    <div class="flex-1">
        @if($title)
            <h2 class="text-lg font-semibold leading-none tracking-tight">
                {{ $title }}
            </h2>
        @endif
        @if($description)
            <p class="text-sm text-muted-foreground mt-1">
                {{ $description }}
            </p>
        @endif
        {{ $slot }}
    </div>
    
    @if($dismissible && $modalId)
        <button type="button" 
                class="ml-4 inline-flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                data-modal-close="{{ $modalId }}"
                aria-label="Close modal">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>