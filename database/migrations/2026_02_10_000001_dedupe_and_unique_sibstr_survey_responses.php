<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SurveyResponse;

return new class extends Migration {
    public function up(): void
    {
        // First, deduplicate by merging data into the latest record per user for 'sibstr'
        DB::transaction(function () {
            // Find users with duplicate SIBSTR rows
            $duplicateUsers = DB::table('survey_responses')
                ->select('user_id', DB::raw('COUNT(*) as cnt'))
                ->where('survey_type', 'sibstr')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('user_id');

            foreach ($duplicateUsers as $userId) {
                // Load all rows for user with casting via model
                $rows = SurveyResponse::where('survey_type', 'sibstr')
                    ->where('user_id', $userId)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->get();

                if ($rows->isEmpty()) {
                    continue;
                }

                $keep = $rows->first();
                $others = $rows->slice(1);

                // Merge fields: fill empty values in $keep with non-empty from older rows
                // Determine all columns in table to consider
                $columns = Schema::getColumnListing('survey_responses');
                $skip = ['id', 'created_at', 'updated_at'];
                foreach ($columns as $column) {
                    if (in_array($column, $skip, true)) {
                        continue;
                    }

                    $current = $keep->$column ?? null;

                    // Use model helper to determine emptiness when possible
                    $isEmpty = method_exists(SurveyResponse::class, 'isEmptyValue')
                        ? SurveyResponse::isEmptyValue($current)
                        : ($current === null || $current === '' || $current === []);

                    if ($isEmpty) {
                        // Seek first non-empty value from the rest
                        foreach ($others as $row) {
                            $candidate = $row->$column ?? null;
                            $candidateEmpty = method_exists(SurveyResponse::class, 'isEmptyValue')
                                ? SurveyResponse::isEmptyValue($candidate)
                                : ($candidate === null || $candidate === '' || $candidate === []);
                            if (!$candidateEmpty) {
                                $keep->$column = $candidate;
                                break;
                            }
                        }
                    }
                }

                // Compute combined meta fields
                $keep->last_saved_at = $rows->max('last_saved_at') ?? $keep->last_saved_at;
                $keep->is_completed = $rows->contains(function ($r) { return (bool) $r->is_completed; }) ? true : (bool) $keep->is_completed;

                $keep->save();

                // Remove the other duplicates
                $idsToDelete = $others->pluck('id')->all();
                if (!empty($idsToDelete)) {
                    SurveyResponse::whereIn('id', $idsToDelete)->delete();
                }
            }
        });

        // Then enforce the unique constraint on (user_id, survey_type)
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->unique(['user_id', 'survey_type'], 'survey_responses_user_type_unique');
        });
    }

    public function down(): void
    {
        // Drop the unique index; we will not re-create duplicates
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropUnique('survey_responses_user_type_unique');
        });
    }
};