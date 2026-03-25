{{-- Example usage of form components --}}
@extends('layouts.app')

@section('title', 'Form Components Examples')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-6">Form Components Examples</h1>

                <form method="POST" action="#" class="space-y-6">
                    @csrf

                    {{-- Input Component Examples --}}
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold">Input Components</h2>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <x-ui.input 
                                name="name" 
                                id="name" 
                                placeholder="Enter your name" 
                                required 
                                :error="$errors->has('name')" />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <x-ui.input 
                                type="email" 
                                name="email" 
                                id="email" 
                                placeholder="Enter your email" 
                                required 
                                :error="$errors->has('email')" />
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <x-ui.input 
                                type="password" 
                                name="password" 
                                id="password" 
                                placeholder="Enter your password" 
                                required 
                                :error="$errors->has('password')" />
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Textarea Component Examples --}}
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold">Textarea Components</h2>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <x-ui.textarea 
                                name="description" 
                                id="description" 
                                placeholder="Enter a description" 
                                rows="4"
                                :error="$errors->has('description')" />
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
                            <x-ui.textarea 
                                name="comments" 
                                id="comments" 
                                placeholder="Optional comments" 
                                rows="3" />
                        </div>
                    </div>

                    {{-- Select Component Examples --}}
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold">Select Components</h2>
                        
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <x-ui.select 
                                name="country" 
                                id="country" 
                                placeholder="Select a country"
                                :options="[
                                    'us' => 'United States',
                                    'ca' => 'Canada',
                                    'uk' => 'United Kingdom',
                                    'au' => 'Australia'
                                ]"
                                required 
                                :error="$errors->has('country')" />
                            @error('country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <x-ui.select 
                                name="category" 
                                id="category" 
                                placeholder="Select a category"
                                :options="[
                                    'Electronics' => [
                                        'phones' => 'Phones',
                                        'laptops' => 'Laptops',
                                        'tablets' => 'Tablets'
                                    ],
                                    'Clothing' => [
                                        'shirts' => 'Shirts',
                                        'pants' => 'Pants',
                                        'shoes' => 'Shoes'
                                    ]
                                ]" />
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <x-ui.select name="status" id="status" value="active">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </x-ui.select>
                        </div>
                    </div>

                    {{-- Disabled State Examples --}}
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold">Disabled State Examples</h2>
                        
                        <div>
                            <label for="disabled_input" class="block text-sm font-medium text-gray-700 mb-1">Disabled Input</label>
                            <x-ui.input 
                                name="disabled_input" 
                                id="disabled_input" 
                                value="This field is disabled" 
                                disabled />
                        </div>

                        <div>
                            <label for="disabled_textarea" class="block text-sm font-medium text-gray-700 mb-1">Disabled Textarea</label>
                            <x-ui.textarea 
                                name="disabled_textarea" 
                                id="disabled_textarea" 
                                value="This textarea is disabled" 
                                disabled />
                        </div>

                        <div>
                            <label for="disabled_select" class="block text-sm font-medium text-gray-700 mb-1">Disabled Select</label>
                            <x-ui.select 
                                name="disabled_select" 
                                id="disabled_select" 
                                :options="['option1' => 'Option 1', 'option2' => 'Option 2']"
                                value="option1"
                                disabled />
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4">
                        <x-ui.button type="submit">
                            Submit Form
                        </x-ui.button>
                        <x-ui.button type="button" variant="outline" class="ml-3">
                            Cancel
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection