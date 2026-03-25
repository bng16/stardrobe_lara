@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Account Settings</h1>
                <p class="mt-1 text-sm text-gray-600">Manage your notification preferences and account visibility</p>
            </div>
            <x-ui.button href="{{ route('profile.show') }}" variant="outline">
                Back to Profile
            </x-ui.button>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <!-- Settings Form -->
        <form method="POST" action="{{ route('profile.settings.update') }}">
            @csrf
            @method('PUT')

            <!-- Email Notifications Card -->
            <x-ui.card>
                <x-ui.card-header>
                    <h2 class="text-xl font-semibold text-gray-900">Email Notifications</h2>
                    <p class="text-sm text-gray-600">Choose which emails you'd like to receive</p>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="space-y-4">
                        <!-- New Products Notification -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <x-ui.checkbox 
                                    name="notify_new_products" 
                                    id="notify_new_products"
                                    :checked="$user->getPreference('notifications.new_products', false)"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="notify_new_products" class="font-medium text-gray-700">
                                    New Products
                                </label>
                                <p class="text-sm text-gray-500">
                                    Get notified when creators you follow list new products
                                </p>
                            </div>
                        </div>

                        <!-- New Followers Notification -->
                        @if($user->role->value === 'creator')
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <x-ui.checkbox 
                                        name="notify_new_followers" 
                                        id="notify_new_followers"
                                        :checked="$user->getPreference('notifications.new_followers', false)"
                                    />
                                </div>
                                <div class="ml-3">
                                    <label for="notify_new_followers" class="font-medium text-gray-700">
                                        New Followers
                                    </label>
                                    <p class="text-sm text-gray-500">
                                        Get notified when someone follows your shop
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Auction Won Notification -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <x-ui.checkbox 
                                    name="notify_auction_won" 
                                    id="notify_auction_won"
                                    :checked="$user->getPreference('notifications.auction_won', false)"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="notify_auction_won" class="font-medium text-gray-700">
                                    Auction Won
                                </label>
                                <p class="text-sm text-gray-500">
                                    Get notified when you win an auction
                                </p>
                            </div>
                        </div>

                        <!-- Auction Sold Notification -->
                        @if($user->role->value === 'creator')
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <x-ui.checkbox 
                                        name="notify_auction_sold" 
                                        id="notify_auction_sold"
                                        :checked="$user->getPreference('notifications.auction_sold', false)"
                                    />
                                </div>
                                <div class="ml-3">
                                    <label for="notify_auction_sold" class="font-medium text-gray-700">
                                        Auction Sold
                                    </label>
                                    <p class="text-sm text-gray-500">
                                        Get notified when your auction ends with a winning bid
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Privacy Settings Card -->
            <x-ui.card class="mt-6">
                <x-ui.card-header>
                    <h2 class="text-xl font-semibold text-gray-900">Privacy Settings</h2>
                    <p class="text-sm text-gray-600">Control who can see your profile information</p>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="space-y-4">
                        <div>
                            <label for="profile_visibility" class="block text-sm font-medium text-gray-700 mb-2">
                                Profile Visibility
                            </label>
                            <select 
                                name="profile_visibility" 
                                id="profile_visibility"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            >
                                <option value="public" {{ $user->getPreference('privacy.profile_visibility', 'public') === 'public' ? 'selected' : '' }}>
                                    Public - Anyone can view your profile
                                </option>
                                <option value="private" {{ $user->getPreference('privacy.profile_visibility', 'public') === 'private' ? 'selected' : '' }}>
                                    Private - Only you can view your full profile
                                </option>
                            </select>
                            <p class="mt-2 text-sm text-gray-500">
                                Control who can see your profile information and activity
                            </p>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Form Actions -->
            <div class="flex justify-end gap-4 mt-6">
                <x-ui.button type="button" variant="outline" href="{{ route('profile.show') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit">
                    Save Settings
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
