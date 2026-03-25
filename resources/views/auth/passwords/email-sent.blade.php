@extends('layouts.auth')

@section('title', __('Check Your Email'))

@php
    $subtitle = __('Check your email');
    $description = __('We\'ve sent a password reset link to your email address');
@endphp

@section('content')
    <div class="text-center space-y-6">
        <!-- Success Icon -->
        <div class="flex justify-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Message -->
        <div class="space-y-2">
            <p class="text-gray-700">
                {{ __('If an account exists with that email address, you will receive a password reset link shortly.') }}
            </p>
            <p class="text-sm text-gray-600">
                {{ __('Please check your spam folder if you don\'t see the email within a few minutes.') }}
            </p>
        </div>

        <!-- Resend Link -->
        <div class="pt-4">
            <p class="text-sm text-gray-600">
                {{ __('Didn\'t receive the email?') }}
                <a href="{{ route('password.request') }}" class="font-medium text-primary hover:text-primary/80 transition-colors duration-200">
                    {{ __('Try again') }}
                </a>
            </p>
        </div>
    </div>
@endsection

@section('additionalLinks')
    @if (Route::has('login'))
        <p class="text-sm text-gray-600">
            <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary/80 transition-colors duration-200">
                {{ __('← Back to login') }}
            </a>
        </p>
    @endif
@endsection
