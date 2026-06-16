<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Records every failed login attempt to the failed_login_attempts table and
 * the application log. Fires for both the Fortify login flow and the legacy
 * AuthController (both go through the guard, which dispatches Auth\Failed).
 *
 * Logging must never break the login response: any storage error is swallowed
 * and reported to the log instead of bubbling up as a 500.
 */
class LogFailedLoginAttempt
{
    public function handle(Failed $event): void
    {
        // Never persist the submitted password — only the username/email.
        $email = is_array($event->credentials)
            ? ($event->credentials['email'] ?? $event->credentials['username'] ?? null)
            : null;

        $request = request();
        $ip = $request?->ip();

        try {
            DB::table('failed_login_attempts')->insert([
                'type'       => 'failed',
                'email'      => $email ? mb_substr($email, 0, 255) : null,
                'ip_address' => $ip,
                'user_agent' => mb_substr((string) $request?->userAgent(), 0, 1000),
                'guard'      => $event->guard,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Table may not exist yet (pre-migration) or DB hiccup — degrade
            // gracefully to a log line, never block the auth response.
            Log::warning('Failed login (audit write failed)', [
                'email' => $email,
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        Log::warning('Failed login attempt', ['email' => $email, 'ip' => $ip]);
    }
}
