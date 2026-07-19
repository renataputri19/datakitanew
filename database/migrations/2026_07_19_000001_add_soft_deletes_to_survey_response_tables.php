<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BPS can delete a single survey submission from the data pages. Deletion is
 * soft so a mis-click is recoverable: the row leaves every list and the user
 * may re-fill the survey, but the answers stay in the table until an admin
 * purges them (UPDATE ... SET deleted_at = NULL restores).
 *
 * Each table is independent — deleting a Listrik submission must never touch
 * the same user's UB or SIBSTR rows.
 *
 * The existing "one row per user per period" unique keys must be rebuilt to
 * include deleted_at. Without that, a soft-deleted row keeps occupying the
 * unique slot, and the user's next getOrCreateForUser() insert dies on a
 * duplicate-key error instead of starting a fresh submission. MySQL treats
 * NULLs in a unique index as distinct, so live rows (deleted_at IS NULL) stay
 * limited to one per period while any number of deleted rows may coexist.
 */
return new class extends Migration
{
    /** table => [unique index name, columns of the live-row uniqueness rule] */
    private const UNIQUES = [
        'survey_responses' => [
            'survey_responses_user_type_period_unique',
            ['user_id', 'survey_type', 'tahun', 'triwulan'],
        ],
        'ub_survey_responses' => [
            'ub_survey_responses_user_id_tahun_unique',
            ['user_id', 'tahun'],
        ],
        'listrik_survey_responses' => [
            'listrik_survey_responses_user_id_tahun_unique',
            ['user_id', 'tahun'],
        ],
    ];

    public function up(): void
    {
        foreach (self::UNIQUES as $table => [$indexName, $columns]) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }

            // The user_id foreign key leans on this unique index as its
            // left-most-prefix index, and MySQL refuses to drop the last index
            // backing an FK (errno 1553). Give the FK a plain index of its own
            // first so the unique key is free to be rebuilt.
            $this->ensureUserIdIndex($table);

            if ($this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropUnique($indexName);
                });
            }

            if (!$this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($indexName, $columns) {
                    $t->unique([...$columns, 'deleted_at'], $indexName);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::UNIQUES as $table => [$indexName, $columns]) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if ($this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropUnique($indexName);
                });
            }

            // Restoring the narrower key requires the trashed rows to be gone,
            // otherwise they collide with the live row they were replaced by.
            if (Schema::hasColumn($table, 'deleted_at')) {
                \DB::table($table)->whereNotNull('deleted_at')->delete();
            }

            Schema::table($table, function (Blueprint $t) use ($indexName, $columns) {
                $t->unique($columns, $indexName);
            });

            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }

    /** Give the user_id FK a dedicated index so the unique key can be dropped. */
    private function ensureUserIdIndex(string $table): void
    {
        $backed = \DB::table('information_schema.statistics')
            ->where('table_schema', \DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('column_name', 'user_id')
            ->where('seq_in_index', 1)
            ->where('non_unique', 1)
            ->exists();

        if ($backed) {
            return;
        }

        Schema::table($table, function (Blueprint $t) {
            $t->index('user_id');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return \DB::table('information_schema.statistics')
            ->where('table_schema', \DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
