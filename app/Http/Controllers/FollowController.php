<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewFollowerNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Follow a creator.
     */
    public function store(Request $request, User $creator): RedirectResponse
    {
        $follower = $request->user();

        // Create follow relationship
        $follower->follows()->attach($creator->id);

        // Dispatch notification to creator
        SendNewFollowerNotification::dispatch($creator, $follower);

        return redirect()->back()->with('success', 'You are now following this creator!');
    }

    /**
     * Unfollow a creator.
     */
    public function destroy(Request $request, User $creator): RedirectResponse
    {
        $follower = $request->user();

        // Remove follow relationship
        $follower->follows()->detach($creator->id);

        return redirect()->back()->with('success', 'You have unfollowed this creator.');
    }
}
