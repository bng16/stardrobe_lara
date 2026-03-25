{{-- Modal Examples --}}
@extends('layouts.app')

@section('title', 'Modal Component Examples')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-3xl font-bold mb-6">Modal Component Examples</h1>
                
                {{-- Basic Modal Example --}}
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Basic Modal</h2>
                    <p class="text-gray-600 mb-4">A simple modal with title, content, and close functionality.</p>
                    
                    <x-ui.button data-modal-trigger="basic_modal_{{ uniqid() }}" variant="default">
                        Open Basic Modal
                    </x-ui.button>
                    
                    <x-ui.modal 
                        id="basic_modal_{{ uniqid() }}"
                        title="Basic Modal"
                        description="This is a basic modal example with default settings."
                        size="md"
                        :dismissible="true">
                        <p class="text-gray-700">
                            This is the modal content. You can put any content here including forms, 
                            images, or other components.
                        </p>
                        <p class="text-gray-700 mt-4">
                            Click the X button, press Escape, or click outside to close this modal.
                        </p>
                        
                        <x-slot name="footer">
                            <x-ui.button variant="outline" data-modal-close="basic_modal_{{ uniqid() }}">
                                Cancel
                            </x-ui.button>
                            <x-ui.button variant="default">
                                Save Changes
                            </x-ui.button>
                        </x-slot>
                    </x-ui.modal>
                </div>
                
                {{-- Different Sizes --}}
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Modal Sizes</h2>
                    <p class="text-gray-600 mb-4">Modals support different sizes: sm, md (default), lg, xl, and full.</p>
                    
                    <div class="flex gap-4 flex-wrap">
                        <x-ui.button data-modal-trigger="small_modal_{{ uniqid() }}" variant="outline">
                            Small Modal
                        </x-ui.button>
                        <x-ui.button data-modal-trigger="medium_modal_{{ uniqid() }}" variant="outline">
                            Medium Modal
                        </x-ui.button>
                        <x-ui.button data-modal-trigger="large_modal_{{ uniqid() }}" variant="outline">
                            Large Modal
                        </x-ui.button>
                        <x-ui.button data-modal-trigger="xl_modal_{{ uniqid() }}" variant="outline">
                            Extra Large Modal
                        </x-ui.button>
                    </div>
                    
                    {{-- Small Modal --}}
                    <x-ui.modal 
                        id="small_modal_{{ uniqid() }}"
                        title="Small Modal"
                        size="sm">
                        <p>This is a small modal (max-width: 24rem).</p>
                    </x-ui.modal>
                    
                    {{-- Medium Modal --}}
                    <x-ui.modal 
                        id="medium_modal_{{ uniqid() }}"
                        title="Medium Modal"
                        size="md">
                        <p>This is a medium modal (max-width: 32rem). This is the default size.</p>
                    </x-ui.modal>
                    
                    {{-- Large Modal --}}
                    <x-ui.modal 
                        id="large_modal_{{ uniqid() }}"
                        title="Large Modal"
                        size="lg">
                        <p>This is a large modal (max-width: 56rem). Perfect for forms or detailed content.</p>
                        <div class="mt-4 p-4 bg-gray-50 rounded">
                            <h3 class="font-medium mb-2">Sample Content</h3>
                            <p>Large modals can contain more complex layouts and content structures.</p>
                        </div>
                    </x-ui.modal>
                    
                    {{-- Extra Large Modal --}}
                    <x-ui.modal 
                        id="xl_modal_{{ uniqid() }}"
                        title="Extra Large Modal"
                        size="xl">
                        <p>This is an extra large modal (max-width: 72rem). Great for dashboards or complex forms.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="p-4 bg-blue-50 rounded">
                                <h3 class="font-medium text-blue-900">Left Column</h3>
                                <p class="text-blue-700">Content for the left side.</p>
                            </div>
                            <div class="p-4 bg-green-50 rounded">
                                <h3 class="font-medium text-green-900">Right Column</h3>
                                <p class="text-green-700">Content for the right side.</p>
                            </div>
                        </div>
                    </x-ui.modal>
                </div>
                
                {{-- Non-dismissible Modal --}}
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Non-dismissible Modal</h2>
                    <p class="text-gray-600 mb-4">A modal that can only be closed through specific actions.</p>
                    
                    <x-ui.button data-modal-trigger="non_dismissible_modal_{{ uniqid() }}" variant="destructive">
                        Open Non-dismissible Modal
                    </x-ui.button>
                    
                    <x-ui.modal 
                        id="non_dismissible_modal_{{ uniqid() }}"
                        title="Confirm Action"
                        description="This action cannot be undone."
                        :dismissible="false"
                        :closeOnBackdrop="false"
                        :closeOnEscape="false">
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Warning</h3>
                                    <p class="text-sm text-red-700 mt-1">
                                        This modal cannot be dismissed by clicking outside or pressing Escape. 
                                        You must choose an action below.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <x-slot name="footer">
                            <x-ui.button variant="outline" onclick="closeModal('non_dismissible_modal_{{ uniqid() }}')">
                                Cancel
                            </x-ui.button>
                            <x-ui.button variant="destructive" onclick="closeModal('non_dismissible_modal_{{ uniqid() }}')">
                                Confirm Delete
                            </x-ui.button>
                        </x-slot>
                    </x-ui.modal>
                </div>
                
                {{-- Form Modal Example --}}
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Form Modal</h2>
                    <p class="text-gray-600 mb-4">A modal containing a form with validation.</p>
                    
                    <x-ui.button data-modal-trigger="form_modal_{{ uniqid() }}" variant="default">
                        Open Form Modal
                    </x-ui.button>
                    
                    <x-ui.modal 
                        id="form_modal_{{ uniqid() }}"
                        title="Create New User"
                        description="Fill out the form below to create a new user account."
                        size="lg">
                        <form class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        First Name
                                    </label>
                                    <x-ui.input 
                                        id="first_name" 
                                        name="first_name" 
                                        type="text" 
                                        placeholder="Enter first name"
                                        required />
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Last Name
                                    </label>
                                    <x-ui.input 
                                        id="last_name" 
                                        name="last_name" 
                                        type="text" 
                                        placeholder="Enter last name"
                                        required />
                                </div>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address
                                </label>
                                <x-ui.input 
                                    id="email" 
                                    name="email" 
                                    type="email" 
                                    placeholder="Enter email address"
                                    required />
                            </div>
                            
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                    Role
                                </label>
                                <x-ui.select id="role" name="role" required>
                                    <option value="">Select a role</option>
                                    <option value="admin">Administrator</option>
                                    <option value="creator">Creator</option>
                                    <option value="user">User</option>
                                </x-ui.select>
                            </div>
                            
                            <div>
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                                    Bio (Optional)
                                </label>
                                <x-ui.textarea 
                                    id="bio" 
                                    name="bio" 
                                    rows="3"
                                    placeholder="Enter a brief bio..." />
                            </div>
                        </form>
                        
                        <x-slot name="footer">
                            <x-ui.button variant="outline" data-modal-close="form_modal_{{ uniqid() }}">
                                Cancel
                            </x-ui.button>
                            <x-ui.button variant="default" type="submit">
                                Create User
                            </x-ui.button>
                        </x-slot>
                    </x-ui.modal>
                </div>
                
                {{-- Programmatic Control --}}
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Programmatic Control</h2>
                    <p class="text-gray-600 mb-4">Control modals programmatically using JavaScript functions.</p>
                    
                    <div class="flex gap-4 flex-wrap">
                        <x-ui.button onclick="openModal('programmatic_modal_{{ uniqid() }}')" variant="default">
                            Open Modal (JS)
                        </x-ui.button>
                        <x-ui.button onclick="closeModal('programmatic_modal_{{ uniqid() }}')" variant="outline">
                            Close Modal (JS)
                        </x-ui.button>
                        <x-ui.button onclick="toggleModal('programmatic_modal_{{ uniqid() }}')" variant="secondary">
                            Toggle Modal (JS)
                        </x-ui.button>
                    </div>
                    
                    <x-ui.modal 
                        id="programmatic_modal_{{ uniqid() }}"
                        title="Programmatically Controlled"
                        description="This modal is controlled via JavaScript functions.">
                        <p>You can control this modal using the following JavaScript functions:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1 text-sm text-gray-600">
                            <li><code class="bg-gray-100 px-1 rounded">openModal('modal_id')</code> - Opens the modal</li>
                            <li><code class="bg-gray-100 px-1 rounded">closeModal('modal_id')</code> - Closes the modal</li>
                            <li><code class="bg-gray-100 px-1 rounded">toggleModal('modal_id')</code> - Toggles the modal</li>
                        </ul>
                        
                        <div class="mt-4 p-3 bg-blue-50 rounded">
                            <p class="text-sm text-blue-800">
                                <strong>Tip:</strong> You can also listen for custom events 'modal:open' and 'modal:close' 
                                to perform actions when modals are opened or closed.
                            </p>
                        </div>
                    </x-ui.modal>
                </div>
                
                {{-- Usage Instructions --}}
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Usage Instructions</h2>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-medium mb-3">Basic Usage</h3>
                        <pre class="bg-white p-4 rounded border text-sm overflow-x-auto"><code>&lt;x-ui.modal 
    id="my_modal"
    title="Modal Title"
    description="Optional description"
    size="md"
    :dismissible="true"&gt;
    &lt;p&gt;Modal content goes here&lt;/p&gt;
    
    &lt;x-slot name="footer"&gt;
        &lt;x-ui.button variant="outline" data-modal-close="my_modal"&gt;
            Cancel
        &lt;/x-ui.button&gt;
        &lt;x-ui.button variant="default"&gt;
            Save
        &lt;/x-ui.button&gt;
    &lt;/x-slot&gt;
&lt;/x-ui.modal&gt;

&lt;!-- Trigger button --&gt;
&lt;x-ui.button data-modal-trigger="my_modal"&gt;
    Open Modal
&lt;/x-ui.button&gt;</code></pre>
                        
                        <h3 class="font-medium mb-3 mt-6">Available Props</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prop</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">id</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">'modal'</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Unique identifier for the modal</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">size</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">'md'</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Modal size: 'sm', 'md', 'lg', 'xl', 'full'</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">dismissible</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">true</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Whether the modal can be dismissed</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">show</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">false</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Whether to show the modal initially</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">backdrop</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">true</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Whether to show backdrop overlay</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">backdropBlur</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">true</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Whether to blur the backdrop</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">closeOnBackdrop</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">true</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Whether clicking backdrop closes modal</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">closeOnEscape</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">boolean</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">true</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Whether Escape key closes modal</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">title</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">null</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Modal title text</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">description</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">null</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">Modal description text</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection