@extends('layouts.auth')

@section('title', __('Reset Password'))

@php
    $subtitle = __('Reset your password');
    $description = __('Enter your new password below');
@endphp

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-6" data-loading>
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email Address -->
        <div>
            <x-ui.label for="email" :required="true">
                {{ __('Email address') }}
            </x-ui.label>
            <x-ui.input
                id="email"
                name="email"
                type="email"
                :value="old('email', $email ?? '')"
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
                {{ __('New Password') }}
            </x-ui.label>
            <div class="relative">
                <x-ui.input
                    id="password"
                    name="password"
                    type="password"
                    :error="$errors->has('password') ? $errors->first('password') : null"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
            </div>
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Must be at least 8 characters long.') }}
            </p>
            @error('password')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Confirmation -->
        <div>
            <x-ui.label for="password_confirmation" :required="true">
                {{ __('Confirm New Password') }}
            </x-ui.label>
            <div class="relative">
                <x-ui.input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    :error="$errors->has('password_confirmation') ? $errors->first('password_confirmation') : null"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
            </div>
            @error('password_confirmation')
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
                {{ __('Reset Password') }}
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
