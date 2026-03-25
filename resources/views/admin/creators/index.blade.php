@extends('layouts.admin')

@section('title', 'Creator Management')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Invite New Creator Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Invite New Creator</h2>
                <p class="text-gray-600">Create a new creator account and send invitation email</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="POST" action="{{ route('admin.creators.store') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Name
                        </label>
                        <x-ui.input 
                            id="name" 
                            name="name" 
                            type="text" 
                            value="{{ old('name') }}"
                            required 
                            :error="$errors->has('name')"
                            class="mt-1" />
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <x-ui.input 
                            id="email" 
                            name="email" 
                            type="email" 
                            value="{{ old('email') }}"
                            required 
                            :error="$errors->has('email')"
                            class="mt-1" />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit">
                        Invite Creator
                    </x-ui.button>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Creators List Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">Creators</h2>
                <p class="text-gray-600">All creator accounts on the platform</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Shop Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($creators as $creator)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">
                                            {{ $creator->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-900">
                                            {{ $creator->email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-900">
                                            {{ $creator->creatorShop->shop_name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($creator->creatorShop && $creator->creatorShop->is_onboarded)
                                                bg-green-100 text-green-800
                                            @else
                                                bg-yellow-100 text-yellow-800
                                            @endif">
                                            @if($creator->creatorShop && $creator->creatorShop->is_onboarded)
                                                Onboarded
                                            @else
                                                Pending Onboarding
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $creator->created_at->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.creators.show', $creator) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No creators found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($creators->hasPages())
                    <div class="mt-6">
                        {{ $creators->links() }}
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
@endsection