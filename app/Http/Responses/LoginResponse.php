<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Keep superadmin users on their dedicated dashboard
        if ($user && $user->is_superadmin) {
            return redirect()->intended(route('superadmin.dashboard'));
        }

        // Redirect all authenticated users to the unified /dashboard
        return redirect()->intended(route('dashboard'));
    }
}
