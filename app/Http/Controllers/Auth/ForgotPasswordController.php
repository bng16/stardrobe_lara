<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ForgotPasswordController extends Controller
{
    /**
     * Display the password reset request form.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.passwords.email');
    }

    /**
     * Handle a password reset link request.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always show the email sent page for security reasons
        // (don't reveal whether the email exists in the system)
        return redirect()->route('password.email.sent')
            ->with('status', __($status));
    }

    /**
     * Display the email sent confirmation page.
     */
    public function showEmailSent(): View
    {
        return view('auth.passwords.email-sent');
    }
}
