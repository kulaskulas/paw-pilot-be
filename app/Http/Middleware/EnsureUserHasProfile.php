<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserProfile;

class EnsureUserHasProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $hasProfile = UserProfile::where('user_id', $user->id)->exists();

        if (!$hasProfile) {
            return response()->json([
                'message' => 'Please complete your profile first.',
                'code' => 'PROFILE_INCOMPLETE',
            ], 403);
        }

        return $next($request);
    }
}
