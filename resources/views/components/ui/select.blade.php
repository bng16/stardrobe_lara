@props([
    'name' => '',
    'id' => '',
    'value' => '',
    'disabled' => false,
    'required' => false,
    'placeholder' => null,
    'options' => [],
    'error' => null
])

@php
// Generate ID from name if not provided
$inputId = $id ?: $name;

// Get old value for form repopulation
$selectedValue = $name ? old($name, $value) : $value;

// Base classes for select
$baseClasses = 'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

// Error state classes
$errorClasses = $error ? 'border-destructive focus:ring-destructive' : '';

// Combine all classes
$classes = trim($baseClasses . ' ' . $errorClasses . ' ' . ($attributes->get('class') ?? ''));

// Prepare attributes excluding our custom props and class
$filteredAttributes = $attributes->except(['class', 'name', 'id', 'value', 'disabled', 'required', 'placeholder', 'options', 'error']);
@endphp

<select name="{{ $name }}"
        @if($inputId) id="{{ $inputId }}" @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        class="{{ $classes }}"
        {{ $filteredAttributes }}>
    
    @if($placeholder)
        <option value="" @selected(empty($selectedValue))>{{ $placeholder }}</option>
    @endif
    
    @if(!empty($options))
        @foreach($options as $optionValue => $optionLabel)
            @if(is_array($optionLabel))
                {{-- Handle option groups --}}
                <optgroup label="{{ $optionValue }}">
                    @foreach($optionLabel as $groupValue => $groupLabel)
                        <option value="{{ $groupValue }}" @selected($selectedValue == $groupValue)>
                            {{ $groupLabel }}
                        </option>
                    @endforeach
                </optgroup>
            @else
                {{-- Handle regular options --}}
                <option value="{{ $optionValue }}" @selected($selectedValue == $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endif
        @endforeach
    @endif
    
    {{-- Allow for custom options via slot --}}
    {{ $slot }}
</select>