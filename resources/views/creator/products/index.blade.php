@extends('layouts.app')

@section('title', 'My Products')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Page Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Products</h1>
                <p class="mt-2 text-sm text-gray-600">Manage your product listings and auctions</p>
            </div>
            <x-ui.button href="{{ route('creator.products.create') }}" variant="default">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Product
            </x-ui.button>
        </div>

        {{-- Search and Filter Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Search & Filter</h2>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="GET" action="{{ route('creator.products.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Search --}}
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input 
                                type="text" 
                                name="search" 
                                id="search" 
                                value="{{ request('search') }}"
                                placeholder="Search by title..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Status Filter --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select 
                                name="status" 
                                id="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                                <option value="unsold" {{ request('status') === 'unsold' ? 'selected' : '' }}>Unsold</option>
                                <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
                            </select>
                        </div>

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
                                placeholder="Filter by category..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Apply Filters
                        </button>
                        <a 
                            href="{{ route('creator.products.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        >
                            Clear Filters
                        </a>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Products Table Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Product Listings</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Total: {{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}
                </p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Reserve Price
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bids
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Auction End
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50">
                                    {{-- Product Column with Image --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($product->images->where('is_primary', true)->first())
                                                <img 
                                                    src="{{ $product->images->where('is_primary', true)->first()->image_path }}" 
                                                    alt="{{ $product->title }}"
                                                    class="h-12 w-12 rounded object-cover mr-3"
                                                >
                                            @else
                                                <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center mr-3">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $product->title }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ Str::limit($product->description, 50) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Category Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->category ?? 'N/A' }}
                                    </td>

                                    {{-- Reserve Price Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($product->reserve_price, 2) }}
                                    </td>

                                    {{-- Status Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($product->status->value === 'active') 
                                                bg-green-100 text-green-800
                                            @elseif($product->status->value === 'sold') 
                                                bg-blue-100 text-blue-800
                                            @elseif($product->status->value === 'unsold') 
                                                bg-gray-100 text-gray-800
                                            @else 
                                                bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ ucfirst($product->status->value) }}
                                        </span>
                                    </td>

                                    {{-- Bids Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->bids_count ?? 0 }}
                                    </td>

                                    {{-- Auction End Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->auction_end->format('M j, Y') }}
                                        <div class="text-xs text-gray-400">
                                            {{ $product->auction_end->format('g:i A') }}
                                        </div>
                                    </td>

                                    {{-- Actions Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a 
                                                href="{{ route('creator.products.edit', $product) }}"
                                                class="text-blue-600 hover:text-blue-900"
                                                title="Edit product"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form 
                                                method="POST" 
                                                action="{{ route('creator.products.destroy', $product) }}"
                                                onsubmit="return confirm('Are you sure you want to delete this product?')"
                                                class="inline"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Delete product"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No products found</h3>
                                            <p class="text-sm text-gray-500 mb-4">
                                                @if(request()->hasAny(['search', 'status', 'category']))
                                                    No products match your filters. Try adjusting your search criteria.
                                                @else
                                                    Get started by creating your first product listing.
                                                @endif
                                            </p>
                                            @if(!request()->hasAny(['search', 'status', 'category']))
                                                <x-ui.button href="{{ route('creator.products.create') }}" variant="default">
                                                    Create Your First Product
                                                </x-ui.button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($products->hasPages())
                    <div class="mt-6">
                        {{ $products->links('components.ui.pagination') }}
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
@endsection
