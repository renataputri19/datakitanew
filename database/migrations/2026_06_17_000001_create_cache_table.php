<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backing tables for the "database" cache store.
 *
 * Switching CACHE_DRIVER from "file" to "database" removes the PHP-FPM
 * race condition in storage/framework/cache/data that intermittently
 * crashed login/register with "fopen(...): Failed to open stream".
 * Guarded with hasTable so it stays idempotent on re-seeded databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
