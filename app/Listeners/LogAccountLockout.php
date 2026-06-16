<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Fortify;

/**
 * Records when the login rate limiter trips (too many failed attempts), so
 * admins can distinguish a real brute-force/spray from a user who simply
 * forgot their password. Enforcement is done by the limiter itself; this is
 * the audit signal.
 */
class LogAccountLockout
{
    public function handle(Lockout $event): void
    {
        $request = $event->request;
        $email = $request?->input(Fortify::username());
        $ip = $request?->ip();

        try {
            DB::table('failed_login_attempts')->insert([
                'type'       => 'lockout',
                'email'      => $email ? mb_substr($email, 0, 255) : null,
                'ip_address' => $ip,
                'user_agent' => mb_substr((string) $request?->userAgent(), 0, 1000),
                'guard'      => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Login lockout (audit write failed)', [
                'email' => $email,
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        Log::warning('Login throttled (lockout)', ['email' => $email, 'ip' => $ip]);
    }
}
