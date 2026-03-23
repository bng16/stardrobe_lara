<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreatorShop;
use Inertia\Inertia;
use Inertia\Response;

class CreatorPrivateInfoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display creator payout information.
     */
    public function show(CreatorShop $shop): Response
    {
        $this->authorize('admin-dashboard');

        $shop->load(['creator', 'privateInfo']);

        return Inertia::render('Admin/Creators/Show', [
            'shop' => $shop,
            'privateInfo' => $shop->privateInfo,
        ]);
    }
}
