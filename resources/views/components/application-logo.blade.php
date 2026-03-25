@props(['class' => ''])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg">
    <!-- Simple logo placeholder - replace with actual logo -->
    <defs>
        <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#1D4ED8;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Background circle -->
    <circle cx="158" cy="158" r="158" fill="url(#logoGradient)"/>
    
    <!-- Letter or icon placeholder -->
    <text x="158" y="180" font-family="Arial, sans-serif" font-size="120" font-weight="bold" text-anchor="middle" fill="white">
        {{ substr(config('app.name', 'A'), 0, 1) }}
    </text>
</svg>