@extends('layouts.app')

@section('title', $shop->shop_name)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="mb-2">
            <a href="{{ route('creator-shop.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Creator Shops
            </a>
        </div>

        <x-ui.card class="overflow-hidden">
            @if($shop->banner_image)
                <img
                    src="{{ $shop->banner_image }}"
                    alt="{{ $shop->shop_name }} banner"
                    class="w-full h-48 object-cover"
                >
            @else
                <div class="w-full h-48 bg-gradient-to-r from-gray-100 to-gray-200"></div>
            @endif

            <x-ui.card-content class="pt-6">
                <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                    <div class="flex items-center gap-4">
                        @if($shop->profile_image)
                            <img
                                src="{{ $shop->profile_image }}"
                                alt="{{ $shop->shop_name }}"
                                class="w-20 h-20 rounded-full object-cover border border-gray-200 bg-white"
                            >
                        @else
                            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center border border-gray-200">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="min-w-0">
                            <h1 class="text-3xl font-bold text-gray-900 truncate">{{ $shop->shop_name }}</h1>
                            <p class="text-sm text-gray-600 mt-1">
                                Followers: <span class="font-semibold text-gray-900">{{ $followerCount }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="sm:ml-auto">
                        @auth
                            @if(auth()->id() !== $creator->id)
                                @if($isFollowing)
                                    <form method="POST" action="{{ route('creators.unfollow', $creator->id) }}" data-loading="true">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                            data-confirm="Unfollow this creator?"
                                        >
                                            Following
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('creators.follow', $creator->id) }}" data-loading="true">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            Follow
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                Log in to follow
                            </a>
                        @endauth
                    </div>
                </div>

                @if($shop->bio)
                    <div class="mt-6">
                        <h2 class="text-lg font-semibold text-gray-900">About</h2>
                        <p class="mt-2 text-gray-700 whitespace-pre-line">{{ $shop->bio }}</p>
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>

        <div>
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-900">Active Auctions</h2>
                <span class="text-sm text-gray-600">{{ $products->total() }} item(s)</span>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($products as $product)
                    @php
                        $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
                    @endphp

                    <a href="{{ route('marketplace.show', $product->id) }}" class="block">
                        <x-ui.card class="hover:shadow-lg transition-shadow cursor-pointer h-full">
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
                                <p class="text-sm text-gray-600 line-clamp-2 mt-1">{{ $product->description }}</p>
                            </x-ui.card-header>

                            <x-ui.card-content>
                                <p class="text-sm text-gray-500">Ends: {{ $product->auction_end->format('M j, Y g:i A') }}</p>
                            </x-ui.card-content>
                        </x-ui.card>
                    </a>
                @empty
                    <div class="col-span-full">
                        <x-ui.card>
                            <x-ui.card-content class="py-12 text-center">
                                <p class="text-gray-500">No active auctions right now.</p>
                            </x-ui.card-content>
                        </x-ui.card>
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->links('components.ui.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

