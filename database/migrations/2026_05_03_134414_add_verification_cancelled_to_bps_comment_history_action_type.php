<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE monalisa_bps_comment_history MODIFY COLUMN action_type ENUM('verified', 'rejected', 'score_updated', 'verification_cancelled') NOT NULL DEFAULT 'verified'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE monalisa_bps_comment_history MODIFY COLUMN action_type ENUM('verified', 'rejected', 'score_updated') NOT NULL DEFAULT 'verified'");
    }
};
