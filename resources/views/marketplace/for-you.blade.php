@extends('layouts.app')

@section('title', 'For You - Marketplace')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Page Header --}}
        <div>
            <h1 class="text-3xl font-bold text-gray-900">For You</h1>
            <p class="mt-2 text-sm text-gray-600">Auctions from creators you follow</p>
        </div>

        @if(!$hasFollows)
            {{-- Empty State: No Follows --}}
            <x-ui.card>
                <x-ui.card-content class="py-12 text-center">
                    <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">You're not following any creators yet</h3>
                    <p class="text-gray-500 mb-4">Start following creators to see their auctions here</p>
                    <a 
                        href="{{ route('marketplace.index') }}" 
                        class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Browse Marketplace
                    </a>
                </x-ui.card-content>
            </x-ui.card>
        @else
            {{-- Products Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($products as $product)
                    @php
                        $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
                    @endphp
                    
                    <a href="{{ route('marketplace.show', $product->id) }}" class="block">
                        <x-ui.card class="hover:shadow-lg transition-shadow cursor-pointer h-full">
                            {{-- Product Image --}}
                            @if($primaryImage)
                                <img 
                                    src="{{ $primaryImage->image_path }}" 
                                    alt="{{ $product->title }}"
                                    class="w-full h-48 object-cover rounded-t-lg"
                                >
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded-t-lg">
                                    <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            <x-ui.card-header>
                                <h3 class="text-lg font-semibold line-clamp-1">{{ $product->title }}</h3>
                                <p class="text-sm text-gray-600 line-clamp-2 mt-1">
                                    {{ $product->description }}
                                </p>
                            </x-ui.card-header>
                            
                            <x-ui.card-content>
                                <div class="space-y-2">
                                    {{-- Creator Info --}}
                                    <div class="flex items-center space-x-2">
                                        @if($product->creator->creatorShop->profile_image)
                                            <img 
                                                src="{{ $product->creator->creatorShop->profile_image }}" 
                                                alt="{{ $product->creator->creatorShop->shop_name }}"
                                                class="w-6 h-6 rounded-full object-cover"
                                            >
                                        @endif
                                        <span class="text-sm text-gray-600">
                                            {{ $product->creator->creatorShop->shop_name }}
                                        </span>
                                    </div>
                                    
                                    {{-- Auction End Time --}}
                                    <p class="text-sm text-gray-500">
                                        Ends: {{ $product->auction_end->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </x-ui.card-content>
                        </x-ui.card>
                    </a>
                @empty
                    <div class="col-span-full">
                        <x-ui.card>
                            <x-ui.card-content class="py-12 text-center">
                                <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-gray-500">No active auctions from creators you follow.</p>
                                <a 
                                    href="{{ route('marketplace.index') }}" 
                                    class="inline-block mt-4 text-blue-600 hover:text-blue-800"
                                >
                                    Browse all auctions →
                                </a>
                            </x-ui.card-content>
                        </x-ui.card>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->links('components.ui.pagination') }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
