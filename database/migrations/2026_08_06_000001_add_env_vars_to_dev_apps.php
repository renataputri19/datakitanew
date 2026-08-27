<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-app environment variables.
 *
 * AppProvisioner::saveEnvironment() replaces an app's whole env block on every
 * deploy, so anything the developer set by hand in Dokploy was wiped on the
 * next one. That made it impossible for an app to hold its own database
 * credentials — which is most of what an app needs an environment for.
 *
 * Stored as raw KEY=value lines rather than JSON: it is what the developer
 * typed, what Dokploy consumes, and what they see again when they come back
 * to edit it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dev_apps', function (Blueprint $table) {
            $table->text('env_vars')->nullable()->after('container_port');
        });
    }

    public function down(): void
    {
        Schema::table('dev_apps', function (Blueprint $table) {
            $table->dropColumn('env_vars');
        });
    }
};
