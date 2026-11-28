<?php

namespace App\Services;

use App\Models\UserProfile;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserProfileService
{
    public function createUserProfile(array $data): UserProfile
    {
        $user = auth()->user();
        $data['user_id'] = $user->id;

        if($user->profile()->exists()) {
            throw new ConflictHttpException('Profile already exists.');
        }

        return UserProfile::create($data);
    }

    public function updateUserProfile(array $data): bool
    {
        $user = auth()->user();
        $userProfile = $user->profile()->firstOrFail();
        return $userProfile->update($data);
    }
}
