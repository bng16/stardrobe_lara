@extends('layouts.app')

@section('title', $product->title . ' - Marketplace')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Back to Marketplace Link --}}
        <div class="mb-6">
            <a href="{{ route('marketplace.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Marketplace
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Left Column: Images Gallery --}}
            <div class="space-y-4">
                @php
                    $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
                    $otherImages = $product->images->where('is_primary', false)->sortBy('display_order');
                @endphp

                {{-- Primary Image --}}
                @if($primaryImage)
                    <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                        <img 
                            src="{{ $primaryImage->image_path }}" 
                            alt="{{ $product->title }}"
                            class="w-full h-full object-cover"
                            id="primary-image"
                        >
                    </div>
                @else
                    <div class="aspect-square rounded-lg bg-gray-200 flex items-center justify-center">
                        <svg class="h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif

                {{-- Image Thumbnails --}}
                @if($product->images->count() > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($product->images->sortBy('display_order') as $image)
                            <button 
                                type="button"
                                class="aspect-square rounded-md overflow-hidden bg-gray-100 border-2 {{ $image->is_primary ? 'border-blue-500' : 'border-transparent' }} hover:border-blue-300 transition-colors"
                                onclick="document.getElementById('primary-image').src='{{ $image->image_path }}'"
                            >
                                <img 
                                    src="{{ $image->image_path }}" 
                                    alt="{{ $product->title }}"
                                    class="w-full h-full object-cover"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right Column: Product Details --}}
            <div class="space-y-6">
                {{-- Product Title and Status --}}
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $product->title }}</h1>
                    <div class="mt-2 flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $product->status->value === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($product->status->value) }}
                        </span>
                        @if($product->category)
                            <span class="text-sm text-gray-500">{{ $product->category }}</span>
                        @endif
                    </div>
                </div>

                {{-- Auction Timing --}}
                <x-ui.card>
                    <x-ui.card-content class="py-4">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Auction Starts:</span>
                                <span class="text-sm font-medium">{{ $product->auction_start->format('M j, Y g:i A') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Auction Ends:</span>
                                <span class="text-sm font-medium text-red-600">{{ $product->auction_end->format('M j, Y g:i A') }}</span>
                            </div>
                            @if($product->isActive())
                                <div class="pt-2 border-t">
                                    <p class="text-sm text-gray-600">
                                        Time remaining: 
                                        <span class="font-medium">{{ $product->auction_end->diffForHumans() }}</span>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Reserve Price --}}
                <x-ui.card>
                    <x-ui.card-content class="py-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Reserve Price:</span>
                            <span class="text-2xl font-bold text-gray-900">${{ number_format($product->reserve_price, 2) }}</span>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- User's Bid Status --}}
                @auth
                    @if($userBid)
                        <x-ui.card class="border-blue-200 bg-blue-50">
                            <x-ui.card-content class="py-4">
                                <h3 class="text-sm font-semibold text-blue-900 mb-2">Your Bid</h3>
                                <div class="space-y-1">
                                    @if(isset($userBid['amount']))
                                        <p class="text-lg font-bold text-blue-900">${{ number_format($userBid['amount'], 2) }}</p>
                                    @endif
                                    <p class="text-sm text-blue-700">
                                        Current Rank: <span class="font-semibold">#{{ $userBid['rank'] }}</span>
                                    </p>
                                </div>
                            </x-ui.card-content>
                        </x-ui.card>
                    @endif
                @endauth

                {{-- Bidding Form --}}
                @auth
                    @if($product->isActive())
                        <x-ui.card>
                            <x-ui.card-header>
                                <h3 class="text-lg font-semibold">Place Your Bid</h3>
                            </x-ui.card-header>
                            <x-ui.card-content>
                                @if(Route::has('bids.store'))
                                    <form method="POST" action="{{ route('bids.store') }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        
                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                                                Bid Amount ($)
                                            </label>
                                            <input 
                                                type="number" 
                                                name="amount" 
                                                id="amount" 
                                                step="0.01"
                                                min="{{ $product->reserve_price }}"
                                                value="{{ old('amount') }}"
                                                required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('amount') border-red-500 @enderror"
                                            >
                                            @error('amount')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                            <p class="mt-1 text-xs text-gray-500">
                                                Minimum bid: ${{ number_format($product->reserve_price, 2) }}
                                            </p>
                                        </div>

                                        <button 
                                            type="submit"
                                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            Place Bid
                                        </button>
                                    </form>
                                @else
                                    <p class="text-sm text-gray-600 text-center py-4">
                                        Bidding functionality coming soon.
                                    </p>
                                @endif
                            </x-ui.card-content>
                        </x-ui.card>
                    @else
                        <x-ui.card class="border-gray-300 bg-gray-50">
                            <x-ui.card-content class="py-4 text-center">
                                <p class="text-sm text-gray-600">
                                    @if($product->hasEnded())
                                        This auction has ended.
                                    @else
                                        This auction has not started yet.
                                    @endif
                                </p>
                            </x-ui.card-content>
                        </x-ui.card>
                    @endif
                @else
                    <x-ui.card class="border-blue-200 bg-blue-50">
                        <x-ui.card-content class="py-4 text-center">
                            <p class="text-sm text-blue-900 mb-3">Please log in to place a bid</p>
                            <a 
                                href="{{ route('login') }}" 
                                class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                Log In
                            </a>
                        </x-ui.card-content>
                    </x-ui.card>
                @endauth

                {{-- Product Description --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="text-lg font-semibold">Description</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <p class="text-gray-700 whitespace-pre-line">{{ $product->description }}</p>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Creator Information --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="text-lg font-semibold">Creator</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="flex items-center space-x-4">
                            @if($product->creator->creatorShop->profile_image)
                                <img 
                                    src="{{ $product->creator->creatorShop->profile_image }}" 
                                    alt="{{ $product->creator->creatorShop->shop_name }}"
                                    class="w-16 h-16 rounded-full object-cover"
                                >
                            @else
                                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $product->creator->creatorShop->shop_name }}</h4>
                                @if($product->creator->creatorShop->bio)
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($product->creator->creatorShop->bio, 100) }}</p>
                                @endif
                                @if(Route::has('creator-shop.show'))
                                    <a 
                                        href="{{ route('creator-shop.show', $product->creator->id) }}" 
                                        class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block"
                                    >
                                        Visit Shop →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
</div>
@endsection
