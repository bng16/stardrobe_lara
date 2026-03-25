<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Header with Export Button -->
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Admin Dashboard</h1>
                <x-ui.button 
                    href="{{ route('admin.auctions.export') }}" 
                    variant="outline">
                    Export All Auctions (JSON)
                </x-ui.button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total Auctions Card -->
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500">Total Auctions</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold">{{ $statistics['total_auctions'] }}</p>
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

                <!-- Sold Auctions Card -->
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500">Sold Auctions</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-blue-600">{{ $statistics['sold_auctions'] }}</p>
                    </div>
                </x-ui.card>

                <!-- Unsold Auctions Card -->
                <x-ui.card>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500">Unsold Auctions</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-gray-600">{{ $statistics['unsold_auctions'] }}</p>
                    </div>
                </x-ui.card>
            </div>

            <!-- Auctions Table -->
            <x-ui.card>
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">All Auctions</h2>
                    <p class="text-gray-600">View and manage all auction listings</p>
                </div>
                <div class="px-6 py-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Creator
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
                                @forelse($auctions as $auction)
                                    <tr>
                                        <!-- Product Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                @if($auction->images->isNotEmpty())
                                                    <img 
                                                        src="{{ $auction->images->first()->image_path }}" 
                                                        alt="{{ $auction->title }}"
                                                        class="w-12 h-12 object-cover rounded"
                                                    >
                                                @else
                                                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <span class="font-medium">{{ $auction->title }}</span>
                                            </div>
                                        </td>

                                        <!-- Creator Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $auction->creator->creatorShop->shop_name ?? 'N/A' }}
                                        </td>

                                        <!-- Status Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($auction->status->value === 'active') 
                                                    bg-green-100 text-green-800
                                                @elseif($auction->status->value === 'sold') 
                                                    bg-blue-100 text-blue-800
                                                @else 
                                                    bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($auction->status->value) }}
                                            </span>
                                        </td>

                                        <!-- Bids Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $auction->bids_count }}
                                        </td>

                                        <!-- Highest Bid Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($auction->highest_bid)
                                                ${{ number_format($auction->highest_bid, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- Ends Column -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ \Carbon\Carbon::parse($auction->auction_end)->format('M j, Y g:i A') }}
                                        </td>

                                        <!-- Actions Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.bids.index', $auction->id) }}" 
                                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                                    View Bids ({{ $auction->bids_count }})
                                                </a>
                                                <span class="text-gray-300">|</span>
                                                <button 
                                                    onclick="exportSingleAuction('{{ $auction->id }}')"
                                                    class="text-green-600 hover:text-green-800 text-sm">
                                                    Export
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No auctions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($auctions->hasPages())
                        <div class="mt-6">
                            {{ $auctions->links() }}
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    <script>
    /**
     * Export a single auction by redirecting to the export endpoint
     */
    function exportSingleAuction(auctionId) {
        window.location.href = "{{ route('admin.auctions.export.single', ':id') }}".replace(':id', auctionId);
    }

    /**
     * Handle export all auctions
     */
    function exportAllAuctions() {
        window.location.href = "{{ route('admin.auctions.export') }}";
    }
    </script>
</body>
</html>