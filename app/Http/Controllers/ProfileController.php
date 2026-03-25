<?php

namespace App\Http\Controllers;

use App\Rules\SecureImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        
        // Load creator shop relationship if user is a creator
        if ($user->role->value === 'creator') {
            $user->load('creatorShop');
        }

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Load creator shop relationship if user is a creator
        if ($user->role->value === 'creator') {
            $user->load('creatorShop');
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Base validation rules for all users
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ];

        // Add creator-specific validation rules
        if ($user->role->value === 'creator' && $user->creatorShop) {
            $rules['shop_name'] = ['required', 'string', 'max:255'];
            $rules['bio'] = ['nullable', 'string', 'max:1000'];
            $rules['profile_image'] = ['nullable', 'image', new SecureImageUpload(5120)];
            $rules['banner_image'] = ['nullable', 'image', new SecureImageUpload(5120)];
        }

        $validated = $request->validate($rules);

        // Update user basic information
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        // Update creator shop information if applicable
        if ($user->role->value === 'creator' && $user->creatorShop) {
            $creatorShop = $user->creatorShop;
            
            $creatorShop->shop_name = $validated['shop_name'];
            $creatorShop->bio = $validated['bio'] ?? null;

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old profile image if exists
                if ($creatorShop->profile_image) {
                    Storage::disk('public')->delete($creatorShop->profile_image);
                }
                
                // Store new profile image
                $path = $request->file('profile_image')->store('creator-profiles', 'public');
                $creatorShop->profile_image = $path;
            }

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                // Delete old banner image if exists
                if ($creatorShop->banner_image) {
                    Storage::disk('public')->delete($creatorShop->banner_image);
                }
                
                // Store new banner image
                $path = $request->file('banner_image')->store('creator-banners', 'public');
                $creatorShop->banner_image = $path;
            }

            $creatorShop->save();
        }

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the form for changing the user's password.
     */
    public function password(Request $request): View
    {
        return view('profile.password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Validate the request
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Verify current password is correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update the password
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Show the form for editing account settings.
     */
    public function settings(Request $request): View
    {
        return view('profile.settings', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's account settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Validate the request
        $validated = $request->validate([
            'notify_new_products' => ['nullable', 'boolean'],
            'notify_new_followers' => ['nullable', 'boolean'],
            'notify_auction_won' => ['nullable', 'boolean'],
            'notify_auction_sold' => ['nullable', 'boolean'],
            'profile_visibility' => ['nullable', 'in:public,private'],
        ]);

        // Update preferences
        $preferences = [
            'notifications' => [
                'new_products' => $validated['notify_new_products'] ?? false,
                'new_followers' => $validated['notify_new_followers'] ?? false,
                'auction_won' => $validated['notify_auction_won'] ?? false,
                'auction_sold' => $validated['notify_auction_sold'] ?? false,
            ],
            'privacy' => [
                'profile_visibility' => $validated['profile_visibility'] ?? 'public',
            ],
        ];

        $user->preferences = $preferences;
        $user->save();

        return redirect()->route('profile.settings')
            ->with('success', 'Settings updated successfully.');
    }
}
