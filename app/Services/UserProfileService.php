<?php

namespace App\Services;

use App\Models\UserProfile;

class UserProfileService
{
    public function createUserProfile(array $data): UserProfile
    {
        $userId = auth()->id();
        $data['user_id'] = $userId;

        return UserProfile::create($data);
    }

    public function updateUserProfile(UserProfile $userProfile, array $data): bool
    {
        return $userProfile->update($data);
    }
}
