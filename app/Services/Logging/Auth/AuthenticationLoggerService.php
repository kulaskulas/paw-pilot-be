<?php

namespace App\Services\Logging\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticationLoggerService {
    public function loginSuccess(int $userId): void
    {
        Log::channel('auth')->info('Login Successfully', [
            'user_id' => $userId
        ]);
    }

    public function loginFailed(string $email): void
    {
        Log::channel('auth')->warning('Login Failed', [
            'email' => $email
        ]);
    }
}
