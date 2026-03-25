@extends('layouts.admin')

@section('title', 'Creator Info - ' . $shop->shop_name)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('admin.creators.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Creators
            </a>
        </div>

        {{-- Creator Information Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Creator Information</h2>
            </x-ui.card-header>
            <x-ui.card-content class="space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Shop Name</p>
                    <p class="text-lg">{{ $shop->shop_name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Creator Name</p>
                    <p class="text-lg">{{ $shop->creator->name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Email</p>
                    <p class="text-lg">{{ $shop->creator->email }}</p>
                </div>
                @if($shop->bio)
                    <div>
                        <p class="text-sm font-medium text-gray-500">Bio</p>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $shop->bio }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-sm font-medium text-gray-500">Onboarding Status</p>
                    <span class="px-2 py-1 text-xs rounded-full 
                        @if($shop->is_onboarded)
                            bg-green-100 text-green-800
                        @else
                            bg-yellow-100 text-yellow-800
                        @endif">
                        @if($shop->is_onboarded)
                            Onboarded
                        @else
                            Pending Onboarding
                        @endif
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Created</p>
                    <p class="text-lg">{{ $shop->created_at->format('M j, Y g:i A') }}</p>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Private Payout Information Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Private Payout Information</h2>
                <p class="text-sm text-red-600 mt-2">
                    Admin Only - Confidential
                </p>
            </x-ui.card-header>
            <x-ui.card-content>
                @if($privateInfo)
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Stripe Account ID</p>
                            <p class="text-lg font-mono">
                                {{ $privateInfo->stripe_account_id ?: 'Not configured' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tax ID</p>
                            <p class="text-lg font-mono">
                                {{ $privateInfo->tax_id ?: 'Not provided' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Payout Email</p>
                            <p class="text-lg">
                                {{ $privateInfo->payout_email ?: 'Not provided' }}
                            </p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500">No private information configured yet.</p>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
@endsection