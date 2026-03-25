@extends('layouts.app')

@section('title', 'Marketplace')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Page Header --}}
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Open Market</h1>
            <p class="mt-2 text-sm text-gray-600">Browse active auctions from all creators</p>
        </div>

        {{-- Search and Filter Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Search & Filters</h2>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="GET" action="{{ route('marketplace.index') }}" class="space-y-4">
                    {{-- Search Bar --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            Search
                        </label>
                        <input 
                            type="text" 
                            name="search" 
                            id="search" 
                            value="{{ request('search') }}"
                            placeholder="Search by product title or description..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Category Filter --}}
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                Category
                            </label>
                            <input 
                                type="text" 
                                name="category" 
                                id="category" 
                                value="{{ request('category') }}"
                                placeholder="e.g., Art, Electronics"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Min Price Filter --}}
                        <div>
                            <label for="min_price" class="block text-sm font-medium text-gray-700 mb-1">
                                Min Price
                            </label>
                            <input 
                                type="number" 
                                name="min_price" 
                                id="min_price" 
                                step="0.01"
                                min="0"
                                value="{{ request('min_price') }}"
                                placeholder="e.g., 10.00"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Max Price Filter --}}
                        <div>
                            <label for="max_price" class="block text-sm font-medium text-gray-700 mb-1">
                                Max Price
                            </label>
                            <input 
                                type="number" 
                                name="max_price" 
                                id="max_price" 
                                step="0.01"
                                min="0"
                                value="{{ request('max_price') }}"
                                placeholder="e.g., 100.00"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Auction Status Filter --}}
                        <div>
                            <label for="auction_status" class="block text-sm font-medium text-gray-700 mb-1">
                                Auction Status
                            </label>
                            <select 
                                name="auction_status" 
                                id="auction_status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">All Active</option>
                                <option value="active" {{ request('auction_status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="ending_soon" {{ request('auction_status') === 'ending_soon' ? 'selected' : '' }}>Ending Soon (24h)</option>
                                <option value="closed" {{ request('auction_status') === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        {{-- Creator Name Filter --}}
                        <div>
                            <label for="creator_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Creator Name
                            </label>
                            <input 
                                type="text" 
                                name="creator_name" 
                                id="creator_name" 
                                value="{{ request('creator_name') }}"
                                placeholder="Search by creator..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    {{-- Active Filters Display --}}
                    @if($hasActiveFilters)
                        <div class="flex flex-wrap gap-2 items-center pt-2 border-t border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Active filters:</span>
                            
                            @if(request('search'))
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                                    Search: "{{ request('search') }}"
                                    <a href="{{ route('marketplace.index', request()->except('search')) }}" class="hover:text-blue-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </span>
                            @endif

                            @if(request('category'))
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                                    Category: {{ request('category') }}
                                    <a href="{{ route('marketplace.index', request()->except('category')) }}" class="hover:text-blue-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </span>
                            @endif

                            @if(request('min_price'))
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                                    Min: ${{ request('min_price') }}
                                    <a href="{{ route('marketplace.index', request()->except('min_price')) }}" class="hover:text-blue-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </span>
                            @endif

                            @if(request('max_price'))
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                                    Max: ${{ request('max_price') }}
                                    <a href="{{ route('marketplace.index', request()->except('max_price')) }}" class="hover:text-blue-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </span>
                            @endif

                            @if(request('auction_status'))
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                                    Status: {{ ucfirst(str_replace('_', ' ', request('auction_status'))) }}
                                    <a href="{{ route('marketplace.index', request()->except('auction_status')) }}" class="hover:text-blue-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </span>
                            @endif

                            @if(request('creator_name'))
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                                    Creator: {{ request('creator_name') }}
                                    <a href="{{ route('marketplace.index', request()->except('creator_name')) }}" class="hover:text-blue-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Apply Filters
                        </button>
                        @if($hasActiveFilters)
                            <a 
                                href="{{ route('marketplace.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            >
                                Clear All Filters
                            </a>
                        @endif
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

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
                            <p class="text-gray-500">No products available at the moment.</p>
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
    </div>
</div>
@endsection
