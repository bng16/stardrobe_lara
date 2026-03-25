@props(['active' => false, 'href' => '#', 'icon' => null])

@php
$classes = ($active ?? false)
    ? 'flex items-center p-2 text-base text-gray-900 rounded-lg bg-gray-100 group transition duration-150 ease-in-out'
    : 'flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group transition duration-150 ease-in-out';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        {!! $icon !!}
    @endif
    <span class="ml-3">{{ $slot }}</span>
</a>