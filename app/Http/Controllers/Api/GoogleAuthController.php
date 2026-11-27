<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Services\Logging\Auth\AuthenticationLoggerService;

class GoogleAuthController extends Controller
{
    public function redirect()
    {

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        // Only accept verified Google email addresses.
        if (!($googleUser->user['verified_email'] ?? false)) {
            abort(403, 'Google email is not verified.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->first();

        if (!$user) {
            // Link an existing account with the same verified email,
            // or create a new account.
            $user = User::firstOrNew([
                'email' => $googleUser->getEmail(),
            ]);

            if (!$user->exists) {
                $user->name = $googleUser->getName();
                $user->password = Hash::make(Str::random(40));
            }

            $user->google_id = $googleUser->getId();
            $user->save();
        }

        // Generate a temporary, one-time authorization code.
        $code = Str::random(64);

        Cache::put(
            'google-login:' . $code,
            $user->id,
            now()->addMinute()
        );

        return redirect(
            config('app.frontend_url') .
            '/auth/google/callback?code=' . $code
        );
    }
}
