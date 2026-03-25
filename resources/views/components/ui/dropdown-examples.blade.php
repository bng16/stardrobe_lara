{{-- Dropdown Component Examples --}}
@extends('layouts.app')

@section('title', 'Dropdown Component Examples')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-3xl font-bold text-gray-900">Dropdown Component Examples</h1>
                <p class="mt-2 text-gray-600">Interactive examples of the Blade dropdown component with various configurations.</p>
            </div>
            
            <div class="p-6 space-y-8">
                {{-- Basic Dropdown --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Basic Dropdown</h2>
                    <div class="flex items-center space-x-4">
                        <x-ui.dropdown id="basic-dropdown">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">
                                    Options
                                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </x-ui.button>
                            </x-slot>
                            
                            <x-ui.dropdown-item href="#profile">View Profile</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#settings">Settings</x-ui.dropdown-item>
                            <x-ui.dropdown-separator />
                            <x-ui.dropdown-item href="#logout" destructive>Sign Out</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>

                {{-- Dropdown with Icons --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Dropdown with Icons</h2>
                    <div class="flex items-center space-x-4">
                        <x-ui.dropdown id="icon-dropdown">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Account
                                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </x-ui.button>
                            </x-slot>
                            
                            <x-ui.dropdown-item href="#profile">
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </x-slot>
                                My Profile
                            </x-ui.dropdown-item>
                            
                            <x-ui.dropdown-item href="#billing">
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </x-slot>
                                Billing
                            </x-ui.dropdown-item>
                            
                            <x-ui.dropdown-item href="#settings">
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </x-slot>
                                Settings
                            </x-ui.dropdown-item>
                            
                            <x-ui.dropdown-separator />
                            
                            <x-ui.dropdown-item href="#logout" destructive>
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </x-slot>
                                Sign Out
                            </x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>

                {{-- Dropdown with Keyboard Shortcuts --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Dropdown with Keyboard Shortcuts</h2>
                    <div class="flex items-center space-x-4">
                        <x-ui.dropdown id="shortcut-dropdown">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">
                                    Actions
                                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </x-ui.button>
                            </x-slot>
                            
                            <x-ui.dropdown-item href="#new" shortcut="⌘N">
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </x-slot>
                                New Item
                            </x-ui.dropdown-item>
                            
                            <x-ui.dropdown-item href="#copy" shortcut="⌘C">
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </x-slot>
                                Copy
                            </x-ui.dropdown-item>
                            
                            <x-ui.dropdown-item href="#paste" shortcut="⌘V">
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </x-slot>
                                Paste
                            </x-ui.dropdown-item>
                            
                            <x-ui.dropdown-separator />
                            
                            <x-ui.dropdown-item href="#delete" destructive shortcut="⌫">
                                <x-slot name="icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </x-slot>
                                Delete
                            </x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>

                {{-- Different Alignments --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Different Alignments</h2>
                    <div class="flex items-center justify-between">
                        {{-- Left Aligned --}}
                        <x-ui.dropdown id="left-dropdown" align="left">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Left Aligned</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item3">Item 3</x-ui.dropdown-item>
                        </x-ui.dropdown>

                        {{-- Center Aligned --}}
                        <x-ui.dropdown id="center-dropdown" align="center">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Center Aligned</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item3">Item 3</x-ui.dropdown-item>
                        </x-ui.dropdown>

                        {{-- Right Aligned --}}
                        <x-ui.dropdown id="right-dropdown" align="right">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Right Aligned</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item3">Item 3</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>

                {{-- Different Positions --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Different Positions</h2>
                    <div class="grid grid-cols-2 gap-8">
                        {{-- Top Position --}}
                        <div class="flex justify-center pt-20">
                            <x-ui.dropdown id="top-dropdown" position="top">
                                <x-slot name="trigger">
                                    <x-ui.button variant="outline">Top Position</x-ui.button>
                                </x-slot>
                                <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                                <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                                <x-ui.dropdown-item href="#item3">Item 3</x-ui.dropdown-item>
                            </x-ui.dropdown>
                        </div>

                        {{-- Bottom Position (Default) --}}
                        <div class="flex justify-center">
                            <x-ui.dropdown id="bottom-dropdown" position="bottom">
                                <x-slot name="trigger">
                                    <x-ui.button variant="outline">Bottom Position</x-ui.button>
                                </x-slot>
                                <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                                <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                                <x-ui.dropdown-item href="#item3">Item 3</x-ui.dropdown-item>
                            </x-ui.dropdown>
                        </div>
                    </div>
                </section>

                {{-- Different Widths --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Different Widths</h2>
                    <div class="flex items-center space-x-4">
                        <x-ui.dropdown id="small-dropdown" width="sm">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Small</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                        </x-ui.dropdown>

                        <x-ui.dropdown id="medium-dropdown" width="md">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Medium</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                        </x-ui.dropdown>

                        <x-ui.dropdown id="large-dropdown" width="lg">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Large</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>

                {{-- Dropdown with Labels and Separators --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Dropdown with Labels and Separators</h2>
                    <div class="flex items-center space-x-4">
                        <x-ui.dropdown id="labeled-dropdown">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">
                                    More Options
                                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </x-ui.button>
                            </x-slot>
                            
                            <x-ui.dropdown-label>Account</x-ui.dropdown-label>
                            <x-ui.dropdown-item href="#profile">Profile</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#billing">Billing</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#team">Team</x-ui.dropdown-item>
                            
                            <x-ui.dropdown-separator />
                            
                            <x-ui.dropdown-label>Support</x-ui.dropdown-label>
                            <x-ui.dropdown-item href="#docs">Documentation</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#support">Contact Support</x-ui.dropdown-item>
                            
                            <x-ui.dropdown-separator />
                            
                            <x-ui.dropdown-item href="#logout" destructive>Sign Out</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>

                {{-- Disabled States --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Disabled States</h2>
                    <div class="flex items-center space-x-4">
                        {{-- Disabled Dropdown --}}
                        <x-ui.dropdown id="disabled-dropdown" disabled>
                            <x-slot name="trigger">
                                <x-ui.button variant="outline" disabled>Disabled Dropdown</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                        </x-ui.dropdown>

                        {{-- Dropdown with Disabled Items --}}
                        <x-ui.dropdown id="disabled-items-dropdown">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Disabled Items</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Available Item</x-ui.dropdown-item>
                            <x-ui.dropdown-item disabled>Disabled Item</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item3">Another Available Item</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>

                {{-- Hover Trigger --}}
                <section>
                    <h2 class="text-xl font-semibold mb-4">Hover Trigger</h2>
                    <div class="flex items-center space-x-4">
                        <x-ui.dropdown id="hover-dropdown" trigger="hover">
                            <x-slot name="trigger">
                                <x-ui.button variant="outline">Hover Me</x-ui.button>
                            </x-slot>
                            <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#item3">Item 3</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

{{-- Demo JavaScript for showing interactions --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add click handlers for demo purposes
        document.addEventListener('click', function(e) {
            const menuItem = e.target.closest('[role="menuitem"]');
            if (menuItem && menuItem.getAttribute('href') && menuItem.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const action = menuItem.getAttribute('href').substring(1);
                
                // Show a simple notification for demo purposes
                showNotification(`Action: ${action}`);
            }
        });
        
        function showNotification(message) {
            // Create a simple notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-md shadow-lg z-50 transition-all duration-300';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    });
</script>
@endpush
@endsection