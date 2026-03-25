@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center mb-2">
                <a href="{{ route('creator.products.index') }}" class="text-gray-600 hover:text-gray-900 mr-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
            </div>
            <p class="text-sm text-gray-600">Update your product details</p>
        </div>

        {{-- Form Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Product Details</h2>
                <p class="text-sm text-gray-500 mt-1">Modify the information about your product</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="POST" action="{{ route('creator.products.update', $product) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Title Field --}}
                    <div>
                        <x-ui.label for="title" required>Product Title</x-ui.label>
                        <x-ui.input 
                            type="text"
                            name="title"
                            id="title"
                            :value="old('title', $product->title)"
                            placeholder="Enter product title"
                            required
                            :error="$errors->has('title')"
                        />
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description Field --}}
                    <div>
                        <x-ui.label for="description" required>Description</x-ui.label>
                        <x-ui.textarea 
                            name="description"
                            id="description"
                            :value="old('description', $product->description)"
                            placeholder="Describe your product in detail..."
                            rows="6"
                            required
                            :error="$errors->has('description')"
                        />
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Maximum 5000 characters</p>
                    </div>

                    {{-- Category Field --}}
                    <div>
                        <x-ui.label for="category">Category</x-ui.label>
                        <x-ui.input 
                            type="text"
                            name="category"
                            id="category"
                            :value="old('category', $product->category)"
                            placeholder="e.g., Art, Collectibles, Fashion"
                            :error="$errors->has('category')"
                        />
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Reserve Price Field --}}
                    <div>
                        <x-ui.label for="reserve_price" required>Reserve Price</x-ui.label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <x-ui.input 
                                type="number"
                                name="reserve_price"
                                id="reserve_price"
                                :value="old('reserve_price', $product->reserve_price)"
                                placeholder="0.00"
                                step="0.01"
                                min="0.01"
                                max="999999.99"
                                required
                                class="pl-7"
                                :error="$errors->has('reserve_price')"
                            />
                        </div>
                        @error('reserve_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Minimum bid amount for this auction</p>
                    </div>

                    {{-- Auction Dates --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Auction Start --}}
                        <div>
                            <x-ui.label for="auction_start" required>Auction Start</x-ui.label>
                            <x-ui.input 
                                type="datetime-local"
                                name="auction_start"
                                id="auction_start"
                                :value="old('auction_start', $product->auction_start->format('Y-m-d\TH:i'))"
                                required
                                :error="$errors->has('auction_start')"
                            />
                            @error('auction_start')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Auction End --}}
                        <div>
                            <x-ui.label for="auction_end" required>Auction End</x-ui.label>
                            <x-ui.input 
                                type="datetime-local"
                                name="auction_end"
                                id="auction_end"
                                :value="old('auction_end', $product->auction_end->format('Y-m-d\TH:i'))"
                                required
                                :error="$errors->has('auction_end')"
                            />
                            @error('auction_end')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Current Product Images --}}
                    @if($product->images->count() > 0)
                        <div>
                            <x-ui.label>Current Images</x-ui.label>
                            <div class="mt-2 grid grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($product->images->sortBy('display_order') as $image)
                                    <div class="relative">
                                        <img 
                                            src="{{ $image->image_path }}" 
                                            alt="{{ $product->title }}"
                                            class="w-full h-24 object-cover rounded-md border border-gray-300"
                                        >
                                        @if($image->is_primary)
                                            <span class="absolute top-1 left-1 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                                                Primary
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Note: Image management is not available in edit mode. To change images, please delete and recreate the product.
                            </p>
                        </div>
                    @endif

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end gap-4 pt-6 border-t">
                        <x-ui.button 
                            type="button" 
                            variant="outline"
                            href="{{ route('creator.products.index') }}"
                        >
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="submit" variant="default">
                            Update Product
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>

@push('scripts')
<script>
    // Form validation for dates
    document.querySelector('form').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('auction_start').value);
        const endDate = new Date(document.getElementById('auction_end').value);

        if (endDate <= startDate) {
            e.preventDefault();
            alert('Auction end date must be after the start date.');
            return false;
        }
    });
</script>
@endpush
@endsection
