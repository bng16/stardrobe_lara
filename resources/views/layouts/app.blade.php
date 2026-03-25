<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'Connecting creators with collectors through unique auction experiences.' }}">
    <meta name="keywords" content="{{ $metaKeywords ?? 'auctions, creators, marketplace, collectibles' }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Connecting creators with collectors through unique auction experiences.' }}">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}">
    <meta property="twitter:description" content="{{ $metaDescription ?? 'Connecting creators with collectors through unique auction experiences.' }}">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Additional Styles -->
    @stack('styles')
    
    <!-- Page-specific styles -->
    @isset($pageStyles)
        <style>
            {!! $pageStyles !!}
        </style>
    @endisset
</head>
<body class="font-sans antialiased {{ $bodyClass ?? 'bg-gray-50' }}">
    <div class="min-h-screen flex flex-col">
        <!-- Skip to main content link for accessibility -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-blue-600 text-white px-4 py-2 z-50">
            Skip to main content
        </a>

        <!-- Main Navigation -->
        @include('layouts.partials.navigation')

        <!-- Page Header (optional) -->
        @if (isset($header))
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1">
            <!-- Flash Messages -->
            @include('layouts.partials.flash-messages')
            
            <!-- Page Content -->
            <div class="{{ $containerClass ?? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6' }}">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        @if (!isset($hideFooter) || !$hideFooter)
            @include('layouts.partials.footer')
        @endif
    </div>

    <!-- Loading Overlay (hidden by default) -->
    <div id="loading-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading...</span>
        </div>
    </div>

    <!-- Additional Scripts -->
    @stack('scripts')
    
    <!-- Page-specific scripts -->
    @isset($pageScripts)
        <script>
            {!! $pageScripts !!}
        </script>
    @endisset

    <!-- Global JavaScript for common functionality -->
    <script>
        // CSRF token for AJAX requests
        window.csrfToken = '{{ csrf_token() }}';
        
        // App configuration
        window.appConfig = {
            name: '{{ config('app.name') }}',
            url: '{{ config('app.url') }}',
            locale: '{{ app()->getLocale() }}'
        };

        // Flash message auto-hide functionality
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[role="alert"]');
            flashMessages.forEach(function(message) {
                // Auto-hide success messages after 5 seconds
                if (message.classList.contains('bg-green-100')) {
                    setTimeout(function() {
                        message.style.display = 'none';
                    }, 5000);
                }
            });
        });

        // Loading overlay functions
        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
            document.getElementById('loading-overlay').classList.add('flex');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
            document.getElementById('loading-overlay').classList.remove('flex');
        }

        // Form submission loading states
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[data-loading="true"]');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    showLoading();
                });
            });
        });

        // AJAX helper function
        function makeAjaxRequest(url, options = {}) {
            const defaultOptions = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            return fetch(url, { ...defaultOptions, ...options })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('AJAX request failed:', error);
                    throw error;
                });
        }

        // Expose globally for use in other scripts
        window.showLoading = showLoading;
        window.hideLoading = hideLoading;
        window.makeAjaxRequest = makeAjaxRequest;
    </script>
</body>
</html>