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

        // Redirect superadmin users to their dashboard
        if ($user && $user->is_superadmin) {
            return redirect()->intended(route('superadmin.dashboard'));
        }

        // Redirect BPS users to their dashboard
        if ($user && $user->is_bps) {
            return redirect()->intended(route('bps.dashboard'));
        }

        // Default redirect for regular users
        return redirect()->intended(route('dashboard'));
    }
}
