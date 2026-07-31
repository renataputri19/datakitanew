<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN / ENUM are MySQL-only; on the sqlite test connection the
        // column is plain text and needs no widening.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE monalisa_bps_comment_history MODIFY COLUMN action_type ENUM('verified', 'rejected', 'score_updated', 'verification_cancelled') NOT NULL DEFAULT 'verified'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE monalisa_bps_comment_history MODIFY COLUMN action_type ENUM('verified', 'rejected', 'score_updated') NOT NULL DEFAULT 'verified'");
    }
};
