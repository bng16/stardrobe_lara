<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Rules\SecureImageUpload;
use App\Services\FileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:creator']);
    }

    /**
     * Display the onboarding form.
     */
    public function show(): Response
    {
        return Inertia::render('Creator/Onboarding');
    }

    /**
     * Store the onboarding information.
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
