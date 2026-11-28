<?php

namespace Tests\Traits;

use App\Models\User;

trait AuthenticatesUsers
{
    protected function authenticateUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $token = $user->createToken('testing')->plainTextToken;

        $this->withToken($token);

        return $user;
    }
}
