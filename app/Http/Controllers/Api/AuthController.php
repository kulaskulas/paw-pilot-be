<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\Logging\Auth\AuthenticationLoggerService;
// use App\Services\Manager\LoggerManagerService;

class AuthController extends Controller
{
    public function __construct(
        private AuthenticationLoggerService $authLoggerService,
        // private LoggerManagerService $loggingManagerService
    )
    {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken($validated['device_name'] ?? 'api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $this->authLoggerService->loginFailed($validated['email']);
            // $this->loggingManagerService->getLoggerService('auth', 'loginFailed', $validated['email']);
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken($validated['device_name'] ?? 'api-token')->plainTextToken;

        $this->authLoggerService->loginSuccess($user->id);
        //  $this->loggingManagerService->getLoggerService('auth', 'loginSuccess', $user->id);

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
