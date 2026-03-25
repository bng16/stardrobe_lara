<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'Secure authentication for ' . config('app.name') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Secure authentication for ' . config('app.name') }}">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

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
    
    <!-- Additional Styles -->
    @stack('styles')
    
    <!-- Page-specific styles -->
    @isset($pageStyles)
        <style>
            {!! $pageStyles !!}
        </style>
    @endisset
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-blue-600 text-white px-4 py-2 z-50 rounded-br-md">
        Skip to main content
    </a>

    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <!-- Header with Logo/Branding -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                @if(isset($logo) && $logo)
                    <img class="h-12 w-auto" src="{{ $logo }}" alt="{{ config('app.name') }} Logo">
                @else
                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-gray-900">{{ config('app.name') }}</span>
                    </div>
                @endif
            </div>
            
            @if(isset($subtitle))
                <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                    {{ $subtitle }}
                </h2>
            @endif
            
            @if(isset($description))
                <p class="mt-2 text-center text-sm text-gray-600">
                    {{ $description }}
                </p>
            @endif
        </div>

        <!-- Main Authentication Card -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl rounded-lg sm:px-10 border border-gray-200">
                <!-- Flash Messages -->
                @include('layouts.partials.flash-messages')
                
                <!-- Main Content -->
                <main id="main-content">
                    {{ $slot ?? '' }}
                    @yield('content')
                </main>
            </div>
            
            <!-- Additional Links/Actions -->
            @if($__env->yieldContent('additionalLinks') || $__env->yieldContent('footerContent'))
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-gradient-to-br from-blue-50 to-indigo-100 text-gray-500">
                                {{ $dividerText ?? 'or' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center space-y-2">
                        @yield('additionalLinks')
                        @yield('footerContent')
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Footer Links -->
        <div class="mt-8 text-center">
            <div class="flex justify-center space-x-6 text-sm text-gray-600">
                @if(Route::has('home'))
                    <a href="{{ route('home', [], false) }}" class="hover:text-gray-900 transition-colors duration-200">
                        ← Back to {{ config('app.name') }}
                    </a>
                @else
                    <a href="{{ url('/') }}" class="hover:text-gray-900 transition-colors duration-200">
                        ← Back to {{ config('app.name') }}
                    </a>
                @endif
                @if(Route::has('privacy'))
                    <a href="{{ route('privacy') }}" class="hover:text-gray-900 transition-colors duration-200">
                        Privacy Policy
                    </a>
                @endif
                @if(Route::has('terms'))
                    <a href="{{ route('terms') }}" class="hover:text-gray-900 transition-colors duration-200">
                        Terms of Service
                    </a>
                @endif
                @if(Route::has('contact'))
                    <a href="{{ route('contact') }}" class="hover:text-gray-900 transition-colors duration-200">
                        Contact Support
                    </a>
                @endif
            </div>
            
            <p class="mt-4 text-xs text-gray-500">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Loading Overlay (hidden by default) -->
    <div id="loading-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4 shadow-xl">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Processing...</span>
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

    <!-- Global JavaScript for authentication functionality -->
    <script>
        // CSRF token for AJAX requests
        window.csrfToken = '{{ csrf_token() }}';
        
        // App configuration
        window.appConfig = {
            name: '{{ config('app.name') }}',
            url: '{{ config('app.url') }}',
            locale: '{{ app()->getLocale() }}'
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Flash message auto-hide functionality
            const flashMessages = document.querySelectorAll('[role="alert"]');
            flashMessages.forEach(function(message) {
                // Auto-hide success messages after 5 seconds
                if (message.classList.contains('bg-green-100')) {
                    setTimeout(function() {
                        message.style.opacity = '0';
                        setTimeout(function() {
                            message.style.display = 'none';
                        }, 300);
                    }, 5000);
                }
            });

            // Form validation enhancement
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                // Add loading state on form submission
                form.addEventListener('submit', function(e) {
                    const submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = submitButton.innerHTML.replace(/^.*$/, 'Processing...');
                        
                        // Show loading overlay for auth forms
                        if (form.hasAttribute('data-loading')) {
                            showLoading();
                        }
                    }
                });

                // Real-time validation feedback
                const inputs = form.querySelectorAll('input[required]');
                inputs.forEach(function(input) {
                    input.addEventListener('blur', function() {
                        validateInput(input);
                    });
                    
                    input.addEventListener('input', function() {
                        // Clear error state on input
                        if (input.classList.contains('border-red-500')) {
                            input.classList.remove('border-red-500');
                            input.classList.add('border-gray-300');
                            
                            const errorMsg = input.parentNode.querySelector('.text-red-600');
                            if (errorMsg && !errorMsg.textContent.includes('{{ __("validation.') {
                                errorMsg.style.display = 'none';
                            }
                        }
                    });
                });
            });

            // Password visibility toggle
            const passwordToggles = document.querySelectorAll('[data-toggle-password]');
            passwordToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    const targetId = toggle.getAttribute('data-toggle-password');
                    const passwordInput = document.getElementById(targetId);
                    
                    if (passwordInput) {
                        const isPassword = passwordInput.type === 'password';
                        passwordInput.type = isPassword ? 'text' : 'password';
                        
                        // Update toggle icon/text
                        const icon = toggle.querySelector('svg');
                        if (icon) {
                            icon.style.transform = isPassword ? 'rotate(180deg)' : 'rotate(0deg)';
                        }
                    }
                });
            });
        });

        // Input validation helper
        function validateInput(input) {
            const value = input.value.trim();
            const type = input.type;
            let isValid = true;
            let errorMessage = '';

            if (input.hasAttribute('required') && !value) {
                isValid = false;
                errorMessage = 'This field is required.';
            } else if (type === 'email' && value && !isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            } else if (type === 'password' && value && value.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters long.';
            }

            // Update input styling
            if (isValid) {
                input.classList.remove('border-red-500');
                input.classList.add('border-gray-300');
            } else {
                input.classList.remove('border-gray-300');
                input.classList.add('border-red-500');
            }

            return isValid;
        }

        // Email validation helper
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Loading overlay functions
        function showLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
        }

        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
        }

        // Expose globally for use in other scripts
        window.showLoading = showLoading;
        window.hideLoading = hideLoading;
        window.validateInput = validateInput;
        window.isValidEmail = isValidEmail;
    </script>
</body>
</html>