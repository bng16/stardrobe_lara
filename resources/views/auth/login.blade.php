@extends('layouts.auth')

@section('title', __('Login'))

@php
    $subtitle = __('Welcome back');
    $description = __('Sign in to your account to continue');
@endphp

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-6" data-loading>
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

        <!-- Password -->
        <div>
            <x-ui.label for="password" :required="true">
                {{ __('Password') }}
            </x-ui.label>
            <div class="relative">
                <x-ui.input
                    id="password"
                    name="password"
                    type="password"
                    :error="$errors->has('password') ? $errors->first('password') : null"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <x-ui.checkbox
                    id="remember"
                    name="remember"
                    :checked="old('remember')"
                />
                <x-ui.label for="remember" class="ml-2 mb-0">
                    {{ __('Remember me') }}
                </x-ui.label>
            </div>

            @if (Route::has('password.request'))
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-primary hover:text-primary/80 transition-colors duration-200">
                        {{ __('Forgot your password?') }}
                    </a>
                </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div>
            <x-ui.button
                type="submit"
                class="w-full"
                variant="default"
                size="default"
            >
                {{ __('Sign in') }}
            </x-ui.button>
        </div>
    </form>
@endsection

@section('additionalLinks')
    @if (Route::has('register'))
        <p class="text-sm text-gray-600">
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" class="font-medium text-primary hover:text-primary/80 transition-colors duration-200">
                {{ __('Sign up') }}
            </a>
        </p>
    @endif
@endsection
