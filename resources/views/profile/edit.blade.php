@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
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

        <!-- Basic Information Form -->
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold text-gray-900">Basic Information</h2>
                <p class="text-sm text-gray-600">Update your account details and personal information</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div>
                        <x-ui.label for="name" :required="true">
                            Full Name
                        </x-ui.label>
                        <x-ui.input
                            id="name"
                            name="name"
                            type="text"
                            :value="old('name', $user->name)"
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

                    <!-- Email -->
                    <div>
                        <x-ui.label for="email" :required="true">
                            Email Address
                        </x-ui.label>
                        <x-ui.input
                            id="email"
                            name="email"
                            type="email"
                            :value="old('email', $user->email)"
                            :error="$errors->has('email') ? $errors->first('email') : null"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                        />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Creator-specific fields -->
                    @if($user->role->value === 'creator' && $user->creatorShop)
                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Creator Shop Information</h3>
                            
                            <!-- Shop Name -->
                            <div class="mb-6">
                                <x-ui.label for="shop_name" :required="true">
                                    Shop Name
                                </x-ui.label>
                                <x-ui.input
                                    id="shop_name"
                                    name="shop_name"
                                    type="text"
                                    :value="old('shop_name', $user->creatorShop->shop_name)"
                                    :error="$errors->has('shop_name') ? $errors->first('shop_name') : null"
                                    required
                                    placeholder="My Creative Shop"
                                />
                                @error('shop_name')
                                    <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Bio -->
                            <div class="mb-6">
                                <x-ui.label for="bio">
                                    Bio
                                </x-ui.label>
                                <x-ui.textarea
                                    id="bio"
                                    name="bio"
                                    rows="4"
                                    :value="old('bio', $user->creatorShop->bio)"
                                    :error="$errors->has('bio') ? $errors->first('bio') : null"
                                    placeholder="Tell us about yourself and your creations..."
                                />
                                @error('bio')
                                    <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Profile Image -->
                            <div class="mb-6">
                                <x-ui.label for="profile_image">
                                    Profile Image
                                </x-ui.label>
                                @if($user->creatorShop->profile_image)
                                    <div class="mt-2 mb-3">
                                        <img src="{{ asset('storage/' . $user->creatorShop->profile_image) }}" 
                                             alt="Current profile image" 
                                             class="h-24 w-24 rounded-full object-cover">
                                        <p class="mt-1 text-xs text-gray-500">Current profile image</p>
                                    </div>
                                @endif
                                <x-ui.input
                                    id="profile_image"
                                    name="profile_image"
                                    type="file"
                                    :error="$errors->has('profile_image') ? $errors->first('profile_image') : null"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Accepted formats: JPEG, PNG, WebP. Max size: 5MB.
                                </p>
                                @error('profile_image')
                                    <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Banner Image -->
                            <div>
                                <x-ui.label for="banner_image">
                                    Banner Image
                                </x-ui.label>
                                @if($user->creatorShop->banner_image)
                                    <div class="mt-2 mb-3">
                                        <img src="{{ asset('storage/' . $user->creatorShop->banner_image) }}" 
                                             alt="Current banner image" 
                                             class="h-32 w-auto rounded-lg object-cover">
                                        <p class="mt-1 text-xs text-gray-500">Current banner image</p>
                                    </div>
                                @endif
                                <x-ui.input
                                    id="banner_image"
                                    name="banner_image"
                                    type="file"
                                    :error="$errors->has('banner_image') ? $errors->first('banner_image') : null"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Accepted formats: JPEG, PNG, WebP. Max size: 5MB.
                                </p>
                                @error('banner_image')
                                    <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <x-ui.button type="submit" variant="default">
                            Save Changes
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Security Section -->
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold text-gray-900">Security</h2>
                <p class="text-sm text-gray-600">Manage your account security settings</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-900">Password</h3>
                        <p class="text-sm text-gray-600">Change your password to keep your account secure</p>
                    </div>
                    <x-ui.button href="{{ route('profile.password') }}" variant="outline">
                        Change Password
                    </x-ui.button>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
@endsection
