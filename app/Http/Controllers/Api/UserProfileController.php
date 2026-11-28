<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserProfileService;
use App\Http\Requests\StoreUserProfileRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserProfileController extends Controller
{
    public function __construct(private UserProfileService $userProfileService)
    {}

    public function store(StoreUserProfileRequest $request): JsonResponse {
        try {
            $validated = $request->validated();
            $this->userProfileService->createUserProfile($validated);

            return response()->json([
                'message' => 'User profile created successfully.',
            ], 201);
        } catch (ConflictHttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function update(StoreUserProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->userProfileService->updateUserProfile($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
        ], 200);
    }



}
