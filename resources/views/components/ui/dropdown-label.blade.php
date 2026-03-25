@props(['class' => ''])

<div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider {{ $class }}" 
     role="presentation"
     {{ $attributes->except(['class']) }}>
    {{ $slot }}
</div>