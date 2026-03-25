@props(['class' => ''])

<div class="my-1 border-t border-gray-200 {{ $class }}" 
     role="separator" 
     {{ $attributes->except(['class']) }}>
</div>