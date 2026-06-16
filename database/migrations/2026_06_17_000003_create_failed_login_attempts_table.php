<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for failed logins and throttle lockouts.
 *
 * Enforcement of the lockout itself is done by the rate limiter (see
 * FortifyServiceProvider). This table is the forensic record: who/where
 * tried to log in and failed, and when the throttle tripped. Useful for
 * spotting brute-force / password-spraying and for incident response.
 * Prune periodically, e.g. `failed_login_attempts` older than 90 days.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('failed_login_attempts')) {
            Schema::create('failed_login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('failed')->index(); // 'failed' | 'lockout'
                $table->string('email')->nullable()->index();
                $table->string('ip_address', 45)->nullable()->index();
                $table->text('user_agent')->nullable();
                $table->string('guard')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_login_attempts');
    }
};
