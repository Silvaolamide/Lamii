<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->onboarding_completed) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please complete onboarding before using this feature.',
                    'code' => 'onboarding_required',
                ], 403);
            }

            return redirect()->route('onboarding.profile');
        }

        return $next($request);
    }
}
