<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserProfileService;
use App\Http\Requests\StoreUserProfileRequest;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    public function __construct(private UserProfileService $userProfileService)
    {}

    public function store(StoreUserProfileRequest $request): JsonResponse {
        $validated = $request->validated();
        $this->userProfileService->createUserProfile($validated);

        return response()->json([
            'message' => 'User profile created successfully.',
        ], 201);
    }



}
