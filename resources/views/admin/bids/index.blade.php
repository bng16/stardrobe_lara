@extends('layouts.admin')

@section('title', 'Bids - ' . $product->title)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Product Information Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">{{ $product->title }}</h2>
                <p class="text-sm text-gray-500 mt-2">
                    by {{ $product->creator->creatorShop->shop_name ?? 'N/A' }}
                </p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="space-y-2">
                    <p>
                        <span class="font-medium">Reserve Price:</span> ${{ number_format($product->reserve_price, 2) }}
                    </p>
                    <p>
                        <span class="font-medium">Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($product->status->value === 'active') 
                                bg-green-100 text-green-800
                            @elseif($product->status->value === 'sold') 
                                bg-blue-100 text-blue-800
                            @else 
                                bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($product->status->value) }}
                        </span>
                    </p>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Filter and Sort Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Filter & Sort Bids</h2>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="GET" action="{{ route('admin.bids.index', $product) }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Bidder Filter --}}
                        <div>
                            <label for="bidder" class="block text-sm font-medium text-gray-700 mb-1">
                                Bidder Name/Email
                            </label>
                            <input 
                                type="text" 
                                name="bidder" 
                                id="bidder" 
                                value="{{ $filters['bidder'] ?? '' }}"
                                placeholder="Search by name or email"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Date From Filter --}}
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                                Date From
                            </label>
                            <input 
                                type="date" 
                                name="date_from" 
                                id="date_from" 
                                value="{{ $filters['date_from'] ?? '' }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Date To Filter --}}
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                                Date To
                            </label>
                            <input 
                                type="date" 
                                name="date_to" 
                                id="date_to" 
                                value="{{ $filters['date_to'] ?? '' }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Min Amount Filter --}}
                        <div>
                            <label for="min_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                Min Amount ($)
                            </label>
                            <input 
                                type="number" 
                                name="min_amount" 
                                id="min_amount" 
                                value="{{ $filters['min_amount'] ?? '' }}"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Max Amount Filter --}}
                        <div>
                            <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                Max Amount ($)
                            </label>
                            <input 
                                type="number" 
                                name="max_amount" 
                                id="max_amount" 
                                value="{{ $filters['max_amount'] ?? '' }}"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        {{-- Sort By --}}
                        <div>
                            <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">
                                Sort By
                            </label>
                            <select 
                                name="sort_by" 
                                id="sort_by"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="amount" {{ $sortBy === 'amount' ? 'selected' : '' }}>Amount</option>
                                <option value="date" {{ $sortBy === 'date' ? 'selected' : '' }}>Date</option>
                                <option value="bidder" {{ $sortBy === 'bidder' ? 'selected' : '' }}>Bidder Name</option>
                            </select>
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                Sort Order
                            </label>
                            <select 
                                name="sort_order" 
                                id="sort_order"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="desc" {{ $sortOrder === 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ $sortOrder === 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
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
                            href="{{ route('admin.bids.index', $product) }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        >
                            Clear Filters
                        </a>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Bids Table Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold">All Bids (Admin View)</h2>
                        <p class="text-sm text-gray-500 mt-2">
                            Full bid visibility with amounts
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a 
                            href="{{ route('admin.bids.export.json', array_merge(['product' => $product], request()->query())) }}"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm"
                        >
                            Export JSON
                        </a>
                        <a 
                            href="{{ route('admin.bids.export.csv', array_merge(['product' => $product], request()->query())) }}"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-sm"
                        >
                            Export CSV
                        </a>
                    </div>
                </div>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bidder
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Submitted
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Updated
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($bids as $bid)
                                <tr class="{{ $bid['rank'] === 1 ? 'bg-yellow-50' : '' }}">
                                    {{-- Rank Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-bold">#{{ $bid['rank'] }}</span>
                                        @if($bid['rank'] === 1)
                                            <span class="ml-2 text-xs text-yellow-600">Winner</span>
                                        @endif
                                    </td>

                                    {{-- Bidder Name Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $bid['user_name'] }}
                                    </td>

                                    {{-- Email Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $bid['user_email'] }}
                                    </td>

                                    {{-- Amount Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap font-medium">
                                        ${{ number_format((float)$bid['amount'], 2) }}
                                    </td>

                                    {{-- Submitted Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($bid['created_at'])->format('M j, Y g:i A') }}
                                    </td>

                                    {{-- Updated Column --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($bid['updated_at'])->format('M j, Y g:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        @if(!empty(array_filter($filters ?? [])))
                                            No bids match your filters.
                                        @else
                                            No bids have been placed yet.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
@endsection
