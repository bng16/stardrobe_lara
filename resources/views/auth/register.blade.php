@extends('layouts.auth')

@section('title', __('Register'))

@php
    $subtitle = __('Create your account');
    $description = __('Join our community of creators and buyers');
@endphp

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-6" data-loading>
        @csrf

        <!-- Name -->
        <div>
            <x-ui.label for="name" :required="true">
                {{ __('Full Name') }}
            </x-ui.label>
            <x-ui.input
                id="name"
                name="name"
                type="text"
                :value="old('name')"
                :error="$errors->has('name') ? $errors->first('name') : null"
                required
                autofocus
                autocomplete="name"
                placeholder="John Doe"
            />
            @error('name')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

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
                autocomplete="email"
                placeholder="you@example.com"
            />
            @error('email')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role Selection -->
        <div>
            <x-ui.label for="role" :required="true">
                {{ __('I want to') }}
            </x-ui.label>
            <x-ui.select
                id="role"
                name="role"
                :value="old('role')"
                :error="$errors->has('role') ? $errors->first('role') : null"
                required
                placeholder="Select your role"
                :options="[
                    'creator' => __('Sell my creations (Creator)'),
                    'buyer' => __('Buy and bid on items (Buyer)')
                ]"
            />
            <p class="mt-1 text-xs text-gray-500">
                {{ __('You can change this later in your profile settings.') }}
            </p>
            @error('role')
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
                {{ __('Confirm Password') }}
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

        <!-- Terms and Conditions -->
        <div class="text-sm text-gray-600">
            <p>
                {{ __('By creating an account, you agree to our') }}
                @if (Route::has('terms'))
                    <a href="{{ route('terms') }}" class="text-primary hover:text-primary/80 transition-colors duration-200" target="_blank">
                        {{ __('Terms of Service') }}
                    </a>
                @else
                    {{ __('Terms of Service') }}
                @endif
                {{ __('and') }}
                @if (Route::has('privacy'))
                    <a href="{{ route('privacy') }}" class="text-primary hover:text-primary/80 transition-colors duration-200" target="_blank">
                        {{ __('Privacy Policy') }}
                    </a>
                @else
                    {{ __('Privacy Policy') }}
                @endif
            </p>
        </div>

        <!-- Submit Button -->
        <div>
            <x-ui.button
                type="submit"
                class="w-full"
                variant="default"
                size="default"
            >
                {{ __('Create Account') }}
            </x-ui.button>
        </div>
    </form>
@endsection

@section('additionalLinks')
    @if (Route::has('login'))
        <p class="text-sm text-gray-600">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary/80 transition-colors duration-200">
                {{ __('Sign in') }}
            </a>
        </p>
    @endif
@endsection
