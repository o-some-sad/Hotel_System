<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Determine which broker to use based on the email
        $broker = $this->getBrokerForEmail($request->email);

        // Send the password reset notification
        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __('A password reset link has been sent to your email address. This link will expire in 60 minutes.'))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Get the broker to be used based on the given email.
     */
    protected function getBrokerForEmail(string $email): string
    {
        // Check each user model to determine the appropriate broker
        if (\App\Models\Client::where('email', $email)->exists()) {
            return 'clients';
        }

        if (\App\Models\Admin::where('email', $email)->exists()) {
            return 'admins';
        }

        if (\App\Models\Manager::where('email', $email)->exists()) {
            return 'managers';
        }

        if (\App\Models\Receptionist::where('email', $email)->exists()) {
            return 'receptionists';
        }

        // Default broker
        return 'users';
    }
}
