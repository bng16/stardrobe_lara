<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Jobs\SendCreatorInviteEmail;
use App\Models\CreatorPrivateInfo;
use App\Models\CreatorShop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class CreatorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of creators.
     */
    public function index(): View
    {
        $creators = User::where('role', UserRole::Creator)
            ->with('creatorShop')
            ->latest()
            ->paginate(20);

        return view('admin.creators.index', compact('creators'));
    }

    /**
     * Display the specified creator.
     */
    public function show(User $creator): View
    {
        // Ensure the user is a creator
        if ($creator->role !== UserRole::Creator) {
            abort(404);
        }

        $shop = $creator->creatorShop()->with('creator')->first();
        $privateInfo = CreatorPrivateInfo::where('creator_shop_id', $shop->id)->first();

        return view('admin.creators.show', compact('shop', 'privateInfo'));
    }

    /**
     * Store a newly created creator account.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        // Generate secure temporary password
        $temporaryPassword = Str::random(16);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'role' => UserRole::Creator,
        ]);

        // Create associated creator shop (not onboarded yet)
        CreatorShop::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'is_onboarded' => false,
        ]);

        // Queue invite email with credentials
        SendCreatorInviteEmail::dispatch($user, $temporaryPassword);

        return redirect()->route('admin.creators.index')
            ->with('success', 'Creator account created and invite email sent.');
    }
}
