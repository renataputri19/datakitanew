<?php

use App\Models\KonstruksiSurveyResponse as K;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * SKTH (tahunan) gained an "Updating Status Perusahaan" preamble, so its path
 * now starts at `status` instead of Blok I, and both instruments read
 * status_perusahaan to decide which blocks apply.
 *
 * Two sets of rows predate that and have the column empty:
 *
 *  - every annual row, because the annual form had no preamble at all;
 *  - quarterly rows created before getOrCreateForUser() started seeding it.
 *
 * An empty status_perusahaan is treated as "not answered yet", which is right
 * for a new row but wrong for these: KonstruksiBlokPath::isComplete() reports
 * their preamble as unfilled, turning an already-submitted survey back into an
 * incomplete one on the BPS listing and on the responden's landing page.
 *
 * The answers are recoverable from the data itself: anyone who filled either
 * form was, by definition, an active company that agreed to answer. So those
 * two are backfilled for every row that has neither.
 *
 * keberadaan_proyek is backfilled to "Ada Proyek Konstruksi" ONLY for rows that
 * were actually worked on. That is the answer their questionnaire already
 * assumed — all the blocks they were being shown — so the backfill changes
 * nothing about what they were asked. An untouched row is left blank on
 * purpose: nobody has answered that question yet, and guessing would silently
 * shorten their form.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = fn () => DB::table('konstruksi_survey_responses');

        $table()->whereNull('status_perusahaan')
            ->update(['status_perusahaan' => K::STATUS_AKTIF]);

        $table()->whereNull('kesediaan_responden')
            ->update(['kesediaan_responden' => K::KESEDIAAN_BERSEDIA]);

        $table()
            ->whereNull('keberadaan_proyek')
            ->where(function ($q) {
                $q->where('is_completed', true)
                  ->orWhereNotNull('last_saved_at');

                foreach (range(1, 10) as $i) {
                    $q->orWhere("blok{$i}_completed", true);
                }
            })
            ->update(['keberadaan_proyek' => K::ADA_PROYEK]);
    }

    /**
     * Only rows still carrying exactly what this migration wrote are cleared —
     * a responden may have answered the preamble themselves since, and that
     * answer is theirs, not ours to drop.
     */
    public function down(): void
    {
        DB::table('konstruksi_survey_responses')
            ->where('status_perusahaan', K::STATUS_AKTIF)
            ->where('kesediaan_responden', K::KESEDIAAN_BERSEDIA)
            ->where('keberadaan_proyek', K::ADA_PROYEK)
            ->update([
                'status_perusahaan'   => null,
                'kesediaan_responden' => null,
                'keberadaan_proyek'   => null,
            ]);
    }
};
