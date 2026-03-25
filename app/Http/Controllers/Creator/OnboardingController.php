<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Rules\SecureImageUpload;
use App\Services\FileSecurityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    /**
     * Display the onboarding form.
     */
    public function show(Request $request): View
    {
        // Get current step from session, default to 1
        $currentStep = $request->session()->get('onboarding_step', 1);
        
        // Get stored form data from session
        $formData = $request->session()->get('onboarding_data', []);
        
        return view('creator.onboarding', [
            'currentStep' => $currentStep,
            'formData' => $formData,
        ]);
    }

    /**
     * Process step submission and navigate between steps.
     */
    public function processStep(Request $request): RedirectResponse
    {
        $currentStep = (int) $request->input('current_step', 1);
        $action = $request->input('action'); // 'next', 'previous', or 'submit'
        
        // Get existing form data from session
        $formData = $request->session()->get('onboarding_data', []);
        
        // Handle navigation
        if ($action === 'previous') {
            // Go back without validation
            $newStep = max(1, $currentStep - 1);
            $request->session()->put('onboarding_step', $newStep);
            return redirect()->route('creator.onboarding');
        }
        
        // Validate current step before proceeding
        $validated = $this->validateStep($request, $currentStep);
        
        // Handle file uploads immediately (don't store in session)
        if ($currentStep === 2 && $request->hasFile('profile_image')) {
            $securityService = app(FileSecurityService::class);
            $scanResult = $securityService->scanFile($request->file('profile_image'));
            
            if (!$scanResult['safe']) {
                return back()->withErrors([
                    'profile_image' => 'File upload rejected: ' . implode(', ', $scanResult['issues'])
                ]);
            }
            
            $profilePath = $request->file('profile_image')->store('creator-profiles', 's3');
            $formData['profile_image_path'] = $profilePath;
        }
        
        if ($currentStep === 3 && $request->hasFile('banner_image')) {
            $securityService = app(FileSecurityService::class);
            $scanResult = $securityService->scanFile($request->file('banner_image'));
            
            if (!$scanResult['safe']) {
                return back()->withErrors([
                    'banner_image' => 'File upload rejected: ' . implode(', ', $scanResult['issues'])
                ]);
            }
            
            $bannerPath = $request->file('banner_image')->store('creator-banners', 's3');
            $formData['banner_image_path'] = $bannerPath;
        }
        
        // Merge validated data with existing form data (excluding file uploads)
        $formData = array_merge($formData, $validated);
        $request->session()->put('onboarding_data', $formData);
        
        if ($action === 'submit') {
            // Final submission - process all data
            return $this->completeOnboarding($request, $formData);
        }
        
        // Move to next step
        $newStep = min(3, $currentStep + 1);
        $request->session()->put('onboarding_step', $newStep);
        
        return redirect()->route('creator.onboarding');
    }

    /**
     * Validate data for a specific step.
     */
    private function validateStep(Request $request, int $step): array
    {
        return match($step) {
            1 => $request->validate([
                'shop_name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'unique:creator_shops,shop_name',
                    'regex:/^[a-zA-Z0-9\s\-\_]+$/', // Alphanumeric, spaces, hyphens, underscores
                ],
                'bio' => [
                    'nullable',
                    'string',
                    'max:1000',
                    'min:10',
                ],
            ], [
                'shop_name.required' => 'Please enter a shop name.',
                'shop_name.min' => 'Shop name must be at least 3 characters.',
                'shop_name.max' => 'Shop name cannot exceed 255 characters.',
                'shop_name.unique' => 'This shop name is already taken. Please choose another.',
                'shop_name.regex' => 'Shop name can only contain letters, numbers, spaces, hyphens, and underscores.',
                'bio.min' => 'Bio must be at least 10 characters if provided.',
                'bio.max' => 'Bio cannot exceed 1000 characters.',
            ]),
            2 => $request->validate([
                'profile_image' => ['nullable', new SecureImageUpload(2048)], // 2MB limit
            ], [
                'profile_image' => 'Profile image must be a valid image file (JPEG, PNG) and not exceed 2MB.',
            ]),
            3 => $request->validate([
                'banner_image' => ['nullable', new SecureImageUpload(5120)], // 5MB limit
            ], [
                'banner_image' => 'Banner image must be a valid image file (JPEG, PNG, WebP) and not exceed 5MB.',
            ]),
            default => [],
        };
    }

    /**
     * Complete the onboarding process.
     */
    private function completeOnboarding(Request $request, array $formData): RedirectResponse
    {
        $shop = $request->user()->creatorShop;
        
        $updateData = [
            'shop_name' => $formData['shop_name'] ?? null,
            'bio' => $formData['bio'] ?? null,
        ];
        
        // Use stored file paths from session
        if (isset($formData['profile_image_path'])) {
            $updateData['profile_image'] = Storage::disk('s3')->url($formData['profile_image_path']);
        }
        
        if (isset($formData['banner_image_path'])) {
            $updateData['banner_image'] = Storage::disk('s3')->url($formData['banner_image_path']);
        }
        
        // Mark as onboarded
        $updateData['is_onboarded'] = true;
        
        $shop->update($updateData);
        
        // Clear session data
        $request->session()->forget(['onboarding_step', 'onboarding_data']);
        
        return redirect()->route('creator.products.index')
            ->with('success', 'Your shop has been set up successfully!');
    }

    /**
     * Store the onboarding information (legacy single-step submission).
     * Kept for backward compatibility with existing tests.
     */
    public function store(Request $request, FileSecurityService $securityService): RedirectResponse
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255|unique:creator_shops,shop_name',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => ['nullable', new SecureImageUpload(2048)], // 2MB limit for profile images
            'banner_image' => ['nullable', new SecureImageUpload(5120)], // 5MB limit for banner images
        ]);

        $shop = $request->user()->creatorShop;

        // Handle profile image upload with security scan
        if ($request->hasFile('profile_image')) {
            $scanResult = $securityService->scanFile($request->file('profile_image'));
            
            if (!$scanResult['safe']) {
                return back()->withErrors([
                    'profile_image' => 'File upload rejected: ' . implode(', ', $scanResult['issues'])
                ])->withInput();
            }

            $profilePath = $request->file('profile_image')->store('creator-profiles', 's3');
            $validated['profile_image'] = Storage::disk('s3')->url($profilePath);
        }

        // Handle banner image upload with security scan
        if ($request->hasFile('banner_image')) {
            $scanResult = $securityService->scanFile($request->file('banner_image'));
            
            if (!$scanResult['safe']) {
                return back()->withErrors([
                    'banner_image' => 'File upload rejected: ' . implode(', ', $scanResult['issues'])
                ])->withInput();
            }

            $bannerPath = $request->file('banner_image')->store('creator-banners', 's3');
            $validated['banner_image'] = Storage::disk('s3')->url($bannerPath);
        }

        // Mark as onboarded
        $validated['is_onboarded'] = true;

        $shop->update($validated);

        return redirect()->route('creator.products.index')
            ->with('success', 'Your shop has been set up successfully!');
    }
}
