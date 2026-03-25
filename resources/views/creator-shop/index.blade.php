@extends('layouts.app')

@section('title', 'Creator Shops')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Creator Shops</h1>
            <p class="mt-2 text-sm text-gray-600">Discover creators, browse their active auctions, and follow your favorites.</p>
        </div>

        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Search</h2>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="GET" action="{{ route('creator-shop.index') }}" class="space-y-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Shop name or bio..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Search
                        </button>
                        @if($hasActiveFilters)
                            <a
                                href="{{ route('creator-shop.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            >
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($shops as $shop)
                <a href="{{ route('creator-shop.show', $shop->creator->id) }}" class="block">
                    <x-ui.card class="hover:shadow-lg transition-shadow cursor-pointer h-full">
                        @if($shop->banner_image)
                            <img
                                src="{{ $shop->banner_image }}"
                                alt="{{ $shop->shop_name }} banner"
                                class="w-full h-32 object-cover rounded-t-lg"
                            >
                        @else
                            <div class="w-full h-32 bg-gradient-to-r from-gray-100 to-gray-200 rounded-t-lg"></div>
                        @endif

                        <x-ui.card-content class="pt-4">
                            <div class="flex items-start gap-4">
                                @if($shop->profile_image)
                                    <img
                                        src="{{ $shop->profile_image }}"
                                        alt="{{ $shop->shop_name }}"
                                        class="w-14 h-14 rounded-full object-cover border border-gray-200 bg-white"
                                    >
                                @else
                                    <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center border border-gray-200">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $shop->shop_name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                        {{ $shop->bio ?: 'No bio yet.' }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700">
                                            Followers: {{ $shop->followers_count ?? 0 }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700">
                                            Active auctions: {{ $shop->active_products_count ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                </a>
            @empty
                <div class="col-span-full">
                    <x-ui.card>
                        <x-ui.card-content class="py-12 text-center">
                            <p class="text-gray-500">No creator shops found.</p>
                        </x-ui.card-content>
                    </x-ui.card>
                </div>
            @endforelse
        </div>

        @if($shops->hasPages())
            <div class="mt-6">
                {{ $shops->links('components.ui.pagination') }}
            </div>
        @endif
    </div>
</div>
@endsection

