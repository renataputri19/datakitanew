<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail of every survey Excel/CSV export from the BPS Data Survei pages,
 * shown to superadmins at /superadmin/log-unduhan.
 *
 * Who downloaded is copied into the row (name, email, role) rather than only
 * referenced, so the trail still reads correctly after an account is renamed,
 * re-roled or deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name');
            $table->string('user_email');
            $table->string('user_role', 20);
            $table->string('survey', 20);          // sibstr | ub | listrik | konstruksi
            $table->string('format', 8);           // xlsx | csv
            $table->string('filename');
            $table->unsignedInteger('row_count');
            $table->json('filters')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['survey', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_logs');
    }
};
