<?php

namespace App\Actions\TwoFactorAuth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CompleteTwoFactorAuthentication
{
    /**
     * Complete the two-factor authentication process.
     *
     * @param  mixed  $user  The user to authenticate
     */
    public function handle(User $user): void
    {
        // Get the remember preference from the session (default to false if not set)
        $remember = Session::pull('login.remember', false);

        // Log the user in with the remember preference
        Auth::login($user, $remember);

        // Clear the session variables used for the 2FA challenge
        Session::forget(['login.id']);
    }
}
