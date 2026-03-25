@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Change Password</h1>
            <x-ui.button href="{{ route('profile.show') }}" variant="outline">
                Cancel
            </x-ui.button>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <!-- Password Change Form -->
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold text-gray-900">Update Password</h2>
                <p class="text-sm text-gray-600">Ensure your account is using a strong password to stay secure</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div>
                        <x-ui.label for="current_password" :required="true">
                            Current Password
                        </x-ui.label>
                        <x-ui.input
                            id="current_password"
                            name="current_password"
                            type="password"
                            :error="$errors->has('current_password') ? $errors->first('current_password') : null"
                            required
                            autofocus
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <x-ui.label for="password" :required="true">
                            New Password
                        </x-ui.label>
                        <x-ui.input
                            id="password"
                            name="password"
                            type="password"
                            :error="$errors->has('password') ? $errors->first('password') : null"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Must be at least 8 characters long.
                        </p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <x-ui.label for="password_confirmation" :required="true">
                            Confirm New Password
                        </x-ui.label>
                        <x-ui.input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            :error="$errors->has('password_confirmation') ? $errors->first('password_confirmation') : null"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <x-ui.button type="submit" variant="default">
                            Update Password
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
@endsection
