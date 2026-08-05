<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records whether a dev app's auth gate is actually installed at the edge.
 *
 * The portal writes each app's Traefik config through Dokploy's API, but
 * Dokploy regenerates that config from the app's own domain settings — so a
 * deploy can silently drop our forwardAuth middleware. The app keeps serving
 * traffic and nothing looks broken; it is simply no longer protected.
 *
 * That is the worst failure mode this feature has, so it gets its own state
 * rather than being inferred: every apply is followed by a read-back, and an
 * app that cannot be positively confirmed as protected is refused traffic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dev_apps', function (Blueprint $table) {
            // unknown | protected | unprotected | unverifiable
            $table->string('routing_status', 20)->default('unknown')->after('status');
            $table->timestamp('routing_checked_at')->nullable()->after('routing_status');
            $table->text('routing_error')->nullable()->after('routing_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('dev_apps', function (Blueprint $table) {
            $table->dropColumn(['routing_status', 'routing_checked_at', 'routing_error']);
        });
    }
};
