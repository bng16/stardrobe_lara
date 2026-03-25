@extends('layouts.auth')

@section('title', __('Reset Password'))

@php
    $subtitle = __('Forgot your password?');
    $description = __('Enter your email address and we\'ll send you a link to reset your password');
@endphp

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6" data-loading>
        @csrf

        <!-- Email Address -->
        <div>
            <x-ui.label for="email" :required="true">
                {{ __('Email address') }}
            </x-ui.label>
            <x-ui.input
                id="email"
                name="email"
                type="email"
                :value="old('email')"
                :error="$errors->has('email') ? $errors->first('email') : null"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
            />
            @error('email')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div>
            <x-ui.button
                type="submit"
                class="w-full"
                variant="default"
                size="default"
            >
                {{ __('Send Password Reset Link') }}
            </x-ui.button>
        </div>
    </form>
@endsection

@section('additionalLinks')
    @if (Route::has('login'))
        <p class="text-sm text-gray-600">
            {{ __('Remember your password?') }}
            <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary/80 transition-colors duration-200">
                {{ __('Back to login') }}
            </a>
        </p>
    @endif
@endsection
