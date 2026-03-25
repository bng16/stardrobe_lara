{{-- Loading Component Examples --}}
@extends('layouts.app')

@section('title', 'Loading Component Examples')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Loading Component Examples</h1>
            <p class="mt-2 text-gray-600">Demonstrations of the loading component with different variants, sizes, and colors.</p>
        </div>

        {{-- Basic Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">Basic Loading Indicators</h2>
                <p class="text-gray-600">Default loading indicators without text</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center space-y-4">
                        <h3 class="font-medium">Spinner</h3>
                        <x-ui.loading variant="spinner" />
                    </div>
                    <div class="text-center space-y-4">
                        <h3 class="font-medium">Dots</h3>
                        <x-ui.loading variant="dots" />
                    </div>
                    <div class="text-center space-y-4">
                        <h3 class="font-medium">Bars</h3>
                        <x-ui.loading variant="bars" />
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Size Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">Different Sizes</h2>
                <p class="text-gray-600">Loading indicators in various sizes</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="space-y-6">
                    <div class="flex items-center justify-around">
                        <div class="text-center space-y-2">
                            <p class="text-sm font-medium">Small</p>
                            <x-ui.loading variant="spinner" size="sm" />
                        </div>
                        <div class="text-center space-y-2">
                            <p class="text-sm font-medium">Medium</p>
                            <x-ui.loading variant="spinner" size="md" />
                        </div>
                        <div class="text-center space-y-2">
                            <p class="text-sm font-medium">Large</p>
                            <x-ui.loading variant="spinner" size="lg" />
                        </div>
                        <div class="text-center space-y-2">
                            <p class="text-sm font-medium">Extra Large</p>
                            <x-ui.loading variant="spinner" size="xl" />
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Color Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">Different Colors</h2>
                <p class="text-gray-600">Loading indicators with various color themes</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div class="text-center space-y-2">
                        <p class="text-sm font-medium">Primary</p>
                        <x-ui.loading color="primary" text="Loading..." />
                    </div>
                    <div class="text-center space-y-2">
                        <p class="text-sm font-medium">Secondary</p>
                        <x-ui.loading color="secondary" text="Loading..." />
                    </div>
                    <div class="text-center space-y-2">
                        <p class="text-sm font-medium">Success</p>
                        <x-ui.loading color="success" text="Loading..." />
                    </div>
                    <div class="text-center space-y-2">
                        <p class="text-sm font-medium">Warning</p>
                        <x-ui.loading color="warning" text="Loading..." />
                    </div>
                    <div class="text-center space-y-2">
                        <p class="text-sm font-medium">Danger</p>
                        <x-ui.loading color="danger" text="Loading..." />
                    </div>
                    <div class="text-center space-y-2 bg-gray-800 rounded p-4">
                        <p class="text-sm font-medium text-white">White</p>
                        <x-ui.loading color="white" text="Loading..." />
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Text Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">With Loading Text</h2>
                <p class="text-gray-600">Loading indicators with descriptive text</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="space-y-6">
                    <x-ui.loading text="Loading dashboard data..." />
                    <x-ui.loading variant="dots" text="Processing your request..." />
                    <x-ui.loading variant="bars" text="Saving changes..." />
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Inline Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">Inline Usage</h2>
                <p class="text-gray-600">Loading indicators used inline with text</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Please wait <x-ui.loading inline size="sm" variant="spinner" /> while we process your request.
                    </p>
                    <p class="text-gray-700">
                        Uploading file <x-ui.loading inline size="sm" variant="dots" /> please don't close this window.
                    </p>
                    <p class="text-gray-700">
                        Analyzing data <x-ui.loading inline size="sm" variant="bars" /> this may take a few moments.
                    </p>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Form Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">Form Integration</h2>
                <p class="text-gray-600">Loading states in forms and buttons</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="max-w-md space-y-4">
                    <x-ui.input name="email" placeholder="Enter your email" />
                    <x-ui.textarea name="message" placeholder="Enter your message" rows="3" />
                    
                    <div class="flex items-center space-x-4">
                        <x-ui.button type="button" onclick="showFormLoading()">
                            Submit Form
                        </x-ui.button>
                        <x-ui.loading id="form-loading" :show="false" size="sm" text="Submitting..." />
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Card Loading Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">Card Loading States</h2>
                <p class="text-gray-600">Loading indicators within card components</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-medium">Statistics</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="py-8">
                                <x-ui.loading text="Loading statistics..." />
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>

                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-medium">Recent Activity</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="py-8">
                                <x-ui.loading variant="dots" text="Loading activity..." />
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Interactive Examples --}}
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-xl font-semibold">Interactive Examples</h2>
                <p class="text-gray-600">Toggle loading states with buttons</p>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <x-ui.button type="button" onclick="toggleLoading('demo-loading-1')">
                            Toggle Spinner
                        </x-ui.button>
                        <x-ui.loading id="demo-loading-1" :show="false" text="Loading..." />
                    </div>

                    <div class="flex items-center space-x-4">
                        <x-ui.button type="button" onclick="toggleLoading('demo-loading-2')">
                            Toggle Dots
                        </x-ui.button>
                        <x-ui.loading id="demo-loading-2" variant="dots" :show="false" text="Processing..." />
                    </div>

                    <div class="flex items-center space-x-4">
                        <x-ui.button type="button" onclick="toggleLoading('demo-loading-3')">
                            Toggle Bars
                        </x-ui.button>
                        <x-ui.loading id="demo-loading-3" variant="bars" :show="false" text="Analyzing..." />
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>

<script>
function showFormLoading() {
    const loading = document.getElementById('form-loading');
    loading.style.display = 'flex';
    
    // Hide after 3 seconds for demo
    setTimeout(() => {
        loading.style.display = 'none';
    }, 3000);
}

function toggleLoading(id) {
    const loading = document.getElementById(id);
    const isVisible = loading.style.display === 'flex';
    loading.style.display = isVisible ? 'none' : 'flex';
}
</script>
@endsection