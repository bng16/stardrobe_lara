@extends('layouts.app')

@section('title', 'Complete Your Shop Setup')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        {{-- Progress Steps --}}
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-4">
                @for ($s = 1; $s <= 3; $s++)
                    <div class="flex items-center">
                        <div 
                            class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $currentStep >= $s ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-300 text-gray-600' }}"
                            aria-label="Step {{ $s }}"
                            aria-current="{{ $currentStep === $s ? 'step' : 'false' }}"
                        >
                            @if ($currentStep > $s)
                                {{-- Checkmark for completed steps --}}
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @else
                                {{ $s }}
                            @endif
                        </div>
                        @if ($s < 3)
                            <div class="w-16 h-1 {{ $currentStep > $s ? 'bg-blue-600' : 'bg-gray-300' }} mx-2 transition-colors duration-300"></div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    Step {{ $currentStep }} of 3
                    @if ($currentStep === 1)
                        - Shop Information
                    @elseif ($currentStep === 2)
                        - Profile Image
                    @else
                        - Banner Image
                    @endif
                </p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2 max-w-md mx-auto">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ ($currentStep / 3) * 100 }}%"></div>
                </div>
            </div>
        </div>

        <x-ui.card>
            <x-ui.card-header>
                @if ($currentStep === 1)
                    <h2 class="text-2xl font-semibold">Shop Information</h2>
                    <p class="text-sm text-gray-600 mt-1">Choose your shop name and write a bio</p>
                @elseif ($currentStep === 2)
                    <h2 class="text-2xl font-semibold">Profile Image</h2>
                    <p class="text-sm text-gray-600 mt-1">Upload a profile image for your shop</p>
                @else
                    <h2 class="text-2xl font-semibold">Banner Image</h2>
                    <p class="text-sm text-gray-600 mt-1">Add a banner image to make your shop stand out</p>
                @endif
            </x-ui.card-header>
            
            <x-ui.card-content>
                {{-- Loading overlay --}}
                <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 shadow-xl">
                        <x-ui.loading variant="spinner" size="lg" text="Processing..." />
                    </div>
                </div>

                <form method="POST" action="{{ route('creator.onboarding.step') }}" enctype="multipart/form-data" class="space-y-6" id="onboarding-form">
                    @csrf
                    <input type="hidden" name="current_step" value="{{ $currentStep }}">

                    {{-- Step 1: Shop Information --}}
                    @if ($currentStep === 1)
                        <div>
                            <label for="shop_name" class="block text-sm font-medium mb-2">
                                Shop Name <span class="text-red-500">*</span>
                            </label>
                            <x-ui.input
                                id="shop_name"
                                name="shop_name"
                                type="text"
                                :value="old('shop_name', $formData['shop_name'] ?? '')"
                                placeholder="Enter your shop name"
                                required
                                :error="$errors->first('shop_name')"
                            />
                            @error('shop_name')
                                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Use letters, numbers, spaces, hyphens, and underscores only (min 3 characters)
                            </p>
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium mb-2">
                                Bio
                            </label>
                            <x-ui.textarea
                                id="bio"
                                name="bio"
                                rows="4"
                                placeholder="Tell buyers about yourself and your creations..."
                                :value="old('bio', $formData['bio'] ?? '')"
                                :error="$errors->first('bio')"
                            />
                            <div class="flex justify-between items-center mt-1">
                                @error('bio')
                                    <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
                                @else
                                    <p class="text-xs text-gray-500">Optional, but recommended (min 10 characters)</p>
                                @enderror
                                <p class="text-sm text-gray-500">
                                    <span id="bio-count">{{ strlen(old('bio', $formData['bio'] ?? '')) }}</span>/1000
                                </p>
                            </div>
                        </div>

                        <x-ui.button type="submit" name="action" value="next" class="w-full" id="submit-btn">
                            <span class="btn-text">Next</span>
                            <span class="btn-loading hidden">
                                <x-ui.loading variant="spinner" size="sm" color="white" inline="true" />
                            </span>
                        </x-ui.button>
                    @endif

                    {{-- Step 2: Profile Image --}}
                    @if ($currentStep === 2)
                        <div>
                            <label for="profile_image" class="block text-sm font-medium mb-2">
                                Profile Image
                            </label>
                            <input
                                id="profile_image"
                                name="profile_image"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('profile_image') border-red-500 @enderror"
                            />
                            <p class="mt-2 text-sm text-gray-500">
                                Max 2MB, JPEG/PNG (optional)
                            </p>
                            @error('profile_image')
                                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                            <div id="profile-preview" class="mt-4 hidden">
                                <p class="text-sm font-medium mb-2">Preview:</p>
                                <img id="profile-preview-img" src="" alt="Profile preview" class="w-32 h-32 object-cover rounded-full border-2 border-gray-300">
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <x-ui.button type="submit" name="action" value="previous" variant="outline" class="flex-1">
                                Back
                            </x-ui.button>
                            <x-ui.button type="submit" name="action" value="next" class="flex-1" id="submit-btn">
                                <span class="btn-text">Next</span>
                                <span class="btn-loading hidden">
                                    <x-ui.loading variant="spinner" size="sm" color="white" inline="true" />
                                </span>
                            </x-ui.button>
                        </div>
                    @endif

                    {{-- Step 3: Banner Image --}}
                    @if ($currentStep === 3)
                        <div>
                            <label for="banner_image" class="block text-sm font-medium mb-2">
                                Banner Image
                            </label>
                            <input
                                id="banner_image"
                                name="banner_image"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('banner_image') border-red-500 @enderror"
                            />
                            <p class="mt-2 text-sm text-gray-500">
                                Max 5MB, JPEG/PNG/WebP (optional)
                            </p>
                            @error('banner_image')
                                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                            <div id="banner-preview" class="mt-4 hidden">
                                <p class="text-sm font-medium mb-2">Preview:</p>
                                <img id="banner-preview-img" src="" alt="Banner preview" class="w-full h-48 object-cover rounded-lg border-2 border-gray-300">
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <x-ui.button type="submit" name="action" value="previous" variant="outline" class="flex-1">
                                Back
                            </x-ui.button>
                            <x-ui.button type="submit" name="action" value="submit" class="flex-1" id="submit-btn">
                                <span class="btn-text">Complete Setup</span>
                                <span class="btn-loading hidden">
                                    <x-ui.loading variant="spinner" size="sm" color="white" inline="true" />
                                </span>
                            </x-ui.button>
                        </div>
                    @endif
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('onboarding-form');
        const bioTextarea = document.getElementById('bio');
        const bioCount = document.getElementById('bio-count');
        const profileImageInput = document.getElementById('profile_image');
        const bannerImageInput = document.getElementById('banner_image');
        const submitBtn = document.getElementById('submit-btn');
        const loadingOverlay = document.getElementById('loading-overlay');
        
        // Bio character counter
        if (bioTextarea && bioCount) {
            bioTextarea.addEventListener('input', function() {
                const length = this.value.length;
                bioCount.textContent = length;
                
                // Visual feedback for character count
                if (length > 950) {
                    bioCount.classList.add('text-red-600', 'font-semibold');
                } else if (length > 900) {
                    bioCount.classList.add('text-yellow-600', 'font-semibold');
                    bioCount.classList.remove('text-red-600');
                } else {
                    bioCount.classList.remove('text-red-600', 'text-yellow-600', 'font-semibold');
                }
            });
        }
        
        // Profile image preview
        if (profileImageInput) {
            profileImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Profile image must not exceed 2MB');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                        alert('Profile image must be JPEG or PNG');
                        this.value = '';
                        return;
                    }
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('profile-preview');
                        const previewImg = document.getElementById('profile-preview-img');
                        previewImg.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Banner image preview
        if (bannerImageInput) {
            bannerImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Banner image must not exceed 5MB');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
                        alert('Banner image must be JPEG, PNG, or WebP');
                        this.value = '';
                        return;
                    }
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('banner-preview');
                        const previewImg = document.getElementById('banner-preview-img');
                        previewImg.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Form submission loading state
        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                // Don't show loading for "previous" action
                const action = document.activeElement.value;
                if (action === 'previous') {
                    return;
                }
                
                // Show loading state
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');
                
                if (btnText && btnLoading) {
                    btnText.classList.add('hidden');
                    btnLoading.classList.remove('hidden');
                }
                
                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                
                // Show loading overlay
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('hidden');
                }
            });
        }
        
        // Client-side validation for step 1
        const shopNameInput = document.getElementById('shop_name');
        if (shopNameInput) {
            shopNameInput.addEventListener('blur', function() {
                const value = this.value.trim();
                const regex = /^[a-zA-Z0-9\s\-\_]+$/;
                
                // Remove any existing error message
                const existingError = this.parentElement.querySelector('.client-error');
                if (existingError) {
                    existingError.remove();
                }
                
                if (value && value.length < 3) {
                    this.classList.add('border-red-500');
                    const error = document.createElement('p');
                    error.className = 'mt-1 text-sm text-red-600 client-error';
                    error.textContent = 'Shop name must be at least 3 characters.';
                    this.parentElement.appendChild(error);
                } else if (value && !regex.test(value)) {
                    this.classList.add('border-red-500');
                    const error = document.createElement('p');
                    error.className = 'mt-1 text-sm text-red-600 client-error';
                    error.textContent = 'Shop name can only contain letters, numbers, spaces, hyphens, and underscores.';
                    this.parentElement.appendChild(error);
                } else {
                    this.classList.remove('border-red-500');
                }
            });
            
            shopNameInput.addEventListener('input', function() {
                // Remove error styling on input
                this.classList.remove('border-red-500');
                const existingError = this.parentElement.querySelector('.client-error');
                if (existingError) {
                    existingError.remove();
                }
            });
        }
    });
</script>
@endpush
@endsection
