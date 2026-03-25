<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creator Dashboard</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Creator Dashboard</h1>
                <x-ui.button 
                    href="{{ route('creator.products.create') }}" 
                    variant="default">
                    Create New Product
                </x-ui.button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total Products Card -->
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500">Total Products</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold">{{ $statistics['total_products'] }}</p>
                    </div>
                </x-ui.card>

                <!-- Active Auctions Card -->
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500">Active Auctions</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-green-600">{{ $statistics['active_auctions'] }}</p>
                    </div>
                </x-ui.card>

                <!-- Sold Items Card -->
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500">Sold Items</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-blue-600">{{ $statistics['sold_items'] }}</p>
                    </div>
                </x-ui.card>

                <!-- Total Revenue Card -->
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-purple-600">${{ number_format($statistics['total_revenue'], 2) }}</p>
                    </div>
                </x-ui.card>
            </div>

            <!-- Recent Products -->
            <x-ui.card>
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold">Recent Products</h2>
                            <p class="text-gray-600">Your latest product listings</p>
                        </div>
                        <x-ui.button 
                            href="{{ route('creator.products.index') }}" 
                            variant="outline"
                            size="sm">
                            View All
                        </x-ui.button>
                    </div>
                </div>
                <div class="px-6 py-4">
                    @if($recentProducts->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bids
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Highest Bid
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ends
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentProducts as $product)
                                        <tr>
                                            <!-- Product Column -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-3">
                                                    @if($product->images->isNotEmpty())
                                                        <img 
                                                            src="{{ $product->images->first()->image_path }}" 
                                                            alt="{{ $product->title }}"
                                                            class="w-12 h-12 object-cover rounded"
                                                        >
                                                    @else
                                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <span class="font-medium">{{ $product->title }}</span>
                                                </div>
                                            </td>

                                            <!-- Status Column -->
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                            </td>

                                            <!-- Bids Column -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $product->bids_count }}
                                            </td>

                                            <!-- Highest Bid Column -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($product->highest_bid)
                                                    ${{ number_format($product->highest_bid, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <!-- Ends Column -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ \Carbon\Carbon::parse($product->auction_end)->format('M j, Y g:i A') }}
                                            </td>

                                            <!-- Actions Column -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('creator.products.edit', $product->id) }}" 
                                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No products yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first product.</p>
                            <div class="mt-6">
                                <x-ui.button href="{{ route('creator.products.create') }}">
                                    Create Product
                                </x-ui.button>
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Recent Bids -->
            <x-ui.card>
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Recent Bids</h2>
                    <p class="text-gray-600">Latest bids on your products</p>
                </div>
                <div class="px-6 py-4">
                    @if($recentBids->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bidder
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Time
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentBids as $bid)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="font-medium">{{ $bid->product_title }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $bid->bidder_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="font-semibold text-green-600">${{ number_format($bid->amount, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($bid->created_at)->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No bids yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Bids on your products will appear here.</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</body>
</html>
