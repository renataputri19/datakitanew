<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fixes a hole opened by 2026_07_19_000001.
 *
 * That migration appended `deleted_at` to each "one submission per user per
 * period" unique key so soft-deleted rows would stop blocking a re-fill. But
 * MySQL/MariaDB skip uniqueness checking for any row that has NULL in an
 * indexed column, and live rows have deleted_at IS NULL — so the key stopped
 * constraining live rows entirely and duplicates became insertable again.
 * (survey_responses has needed a dedupe migration before; this is not
 * theoretical.)
 *
 * The standard fix: index a generated column that is 1 while the row is live
 * and NULL once it is deleted. Live rows then collide as intended, deleted
 * rows stay exempt, and Laravel's SoftDeletes keeps working untouched.
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

            $this->dropDuplicateLiveRows($table, $columns);

            if (!Schema::hasColumn($table, 'active_flag')) {
                DB::statement("ALTER TABLE `{$table}`
                    ADD COLUMN `active_flag` TINYINT(1)
                    GENERATED ALWAYS AS (IF(`deleted_at` IS NULL, 1, NULL)) STORED");
            }

            if ($this->indexExists($table, $indexName)) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            }

            $cols = implode('`, `', [...$columns, 'active_flag']);
            DB::statement("ALTER TABLE `{$table}` ADD UNIQUE `{$indexName}` (`{$cols}`)");
        }
    }

    public function down(): void
    {
        foreach (self::UNIQUES as $table => [$indexName, $columns]) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if ($this->indexExists($table, $indexName)) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            }

            if (Schema::hasColumn($table, 'active_flag')) {
                DB::statement("ALTER TABLE `{$table}` DROP COLUMN `active_flag`");
            }

            $cols = implode('`, `', [...$columns, 'deleted_at']);
            DB::statement("ALTER TABLE `{$table}` ADD UNIQUE `{$indexName}` (`{$cols}`)");
        }
    }

    /**
     * Any duplicate live rows created while the key was toothless must go
     * before the stricter index can be applied. Keeps the most recently
     * updated row of each group and soft-deletes the rest, so nothing is
     * destroyed outright.
     */
    private function dropDuplicateLiveRows(string $table, array $columns): void
    {
        $groupBy = implode(', ', $columns);

        $dupes = DB::table($table)
            ->selectRaw($groupBy)
            ->whereNull('deleted_at')
            ->groupByRaw($groupBy)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupes as $dupe) {
            $keep = DB::table($table)->whereNull('deleted_at');
            foreach ($columns as $c) {
                $keep->where($c, $dupe->{$c});
            }
            $keepId = $keep->orderByDesc('updated_at')->value('id');

            $kill = DB::table($table)->whereNull('deleted_at')->where('id', '!=', $keepId);
            foreach ($columns as $c) {
                $kill->where($c, $dupe->{$c});
            }
            $kill->update(['deleted_at' => now()]);
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
