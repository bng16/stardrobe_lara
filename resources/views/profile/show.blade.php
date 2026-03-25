@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
            <x-ui.button href="{{ route('profile.edit') }}" variant="outline">
                Edit Profile
            </x-ui.button>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <!-- Basic Information Card -->
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold text-gray-900">Basic Information</h2>
                <p class="text-sm text-gray-600">Your account details and personal information</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Account Role</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($user->role->value === 'admin') bg-purple-100 text-purple-800
                                @elseif($user->role->value === 'creator') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($user->role->value) }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('F j, Y') }}</dd>
                    </div>
                </dl>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Creator Information Card (only for creators) -->
        @if($user->role->value === 'creator' && $user->creatorShop)
            <x-ui.card>
                <x-ui.card-header>
                    <h2 class="text-xl font-semibold text-gray-900">Creator Information</h2>
                    <p class="text-sm text-gray-600">Your creator shop details and settings</p>
                </x-ui.card-header>
                <x-ui.card-content>
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Shop Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->creatorShop->shop_name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Onboarding Status</dt>
                            <dd class="mt-1">
                                @if($user->creatorShop->is_onboarded)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </dd>
                        </div>
                        
                        @if($user->creatorShop->bio)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Bio</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->creatorShop->bio }}</dd>
                            </div>
                        @endif
                        
                        @if($user->creatorShop->profile_image)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Profile Image</dt>
                                <dd class="mt-2">
                                    <img src="{{ asset('storage/' . $user->creatorShop->profile_image) }}" 
                                         alt="Profile image" 
                                         class="h-20 w-20 rounded-full object-cover">
                                </dd>
                            </div>
                        @endif
                        
                        @if($user->creatorShop->banner_image)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Banner Image</dt>
                                <dd class="mt-2">
                                    <img src="{{ asset('storage/' . $user->creatorShop->banner_image) }}" 
                                         alt="Banner image" 
                                         class="h-20 w-auto rounded-lg object-cover">
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card-content>
            </x-ui.card>
        @endif

        <!-- Account Actions Card -->
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold text-gray-900">Account Actions</h2>
                <p class="text-sm text-gray-600">Manage your account settings and preferences</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Update Profile</h3>
                            <p class="text-sm text-gray-500">Edit your personal information and settings</p>
                        </div>
                        <x-ui.button href="{{ route('profile.edit') }}" variant="outline" size="sm">
                            Edit
                        </x-ui.button>
                    </div>
                    
                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Account Settings</h3>
                            <p class="text-sm text-gray-500">Manage notifications and privacy preferences</p>
                        </div>
                        <x-ui.button href="{{ route('profile.settings') }}" variant="outline" size="sm">
                            Settings
                        </x-ui.button>
                    </div>
                    
                    @if($user->role->value === 'creator' && !$user->creatorShop?->is_onboarded)
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Complete Onboarding</h3>
                                <p class="text-sm text-gray-500">Finish setting up your creator shop</p>
                            </div>
                            <x-ui.button href="{{ route('creator.onboarding') }}" variant="default" size="sm">
                                Continue
                            </x-ui.button>
                        </div>
                    @endif
                    
                    @if($user->role->value === 'creator')
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Creator Dashboard</h3>
                                <p class="text-sm text-gray-500">Manage your products and sales</p>
                            </div>
                            <x-ui.button href="{{ route('creator.dashboard') }}" variant="outline" size="sm">
                                Go to Dashboard
                            </x-ui.button>
                        </div>
                    @endif
                    
                    @if($user->role->value === 'admin')
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Admin Dashboard</h3>
                                <p class="text-sm text-gray-500">Manage platform and users</p>
                            </div>
                            <x-ui.button href="{{ route('admin.dashboard') }}" variant="outline" size="sm">
                                Go to Dashboard
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
@endsection
