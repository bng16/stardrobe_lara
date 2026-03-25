{{-- Card Component Demo --}}
@extends('layouts.app')

@section('title', 'Card Components Demo')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <h1 class="text-3xl font-bold">Card Components Demo</h1>
        
        {{-- Basic Card --}}
        <x-ui.card>
            <x-ui.card-header>
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Basic Card</h3>
                <p class="text-sm text-muted-foreground">This is a basic card example</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <p>This is the card content area. It contains the main information or functionality of the card.</p>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Card with Custom Classes --}}
        <x-ui.card class="border-blue-200 bg-blue-50">
            <x-ui.card-header>
                <h3 class="text-2xl font-semibold leading-none tracking-tight text-blue-900">Custom Styled Card</h3>
                <p class="text-sm text-blue-700">This card has custom styling applied</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <p class="text-blue-800">This card demonstrates how custom classes can be applied to modify the appearance.</p>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Statistics Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.card>
                <x-ui.card-header class="pb-2">
                    <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-3xl font-bold">1,234</p>
                    <p class="text-sm text-green-600">+12% from last month</p>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card-header class="pb-2">
                    <h3 class="text-sm font-medium text-gray-500">Active Auctions</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-3xl font-bold">567</p>
                    <p class="text-sm text-blue-600">+5% from last month</p>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card-header class="pb-2">
                    <h3 class="text-sm font-medium text-gray-500">Revenue</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-3xl font-bold">$89,012</p>
                    <p class="text-sm text-red-600">-2% from last month</p>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection