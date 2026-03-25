<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewFollowerNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Follow a creator.
     */
    public function store(Request $request, User $creator): RedirectResponse
    {
        $follower = $request->user();

        if ($follower->id === $creator->id) {
            return redirect()->back()->with('error', 'You cannot follow yourself.');
        }

        // Create follow relationship
        $wasAttached = $follower->follows()->syncWithoutDetaching([$creator->id]);

        // Dispatch notification to creator
        if (!empty($wasAttached['attached'])) {
            SendNewFollowerNotification::dispatch($creator, $follower);
        }

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
