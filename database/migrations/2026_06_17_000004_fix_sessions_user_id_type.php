<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs sessions.user_id on databases where create_sessions_table already
 * ran with the buggy bigint column (it should be a string, because users.id
 * is a UUID). Writing a UUID into a bigint silently fails, so the database
 * session driver never persists logins. This converts the column in place.
 *
 * Idempotent: skips when the table/column is missing, when not on MySQL, or
 * when the column is already a non-integer type (fresh installs created it as
 * a string via the corrected create migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return; // MODIFY syntax below is MySQL-specific; this project is MySQL.
        }

        $column = DB::selectOne(
            "SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'sessions'
               AND COLUMN_NAME = 'user_id'"
        );

        // Only convert the old integer column; leave correct string columns alone.
        if ($column && str_contains(strtolower($column->DATA_TYPE), 'int')) {
            // MODIFY keeps the existing sessions_user_id_index in place.
            DB::statement('ALTER TABLE `sessions` MODIFY `user_id` VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        // No-op: reverting to bigint would re-break UUID session persistence.
    }
};
