<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // Q207 Tahunan 2025 — detailed worker breakdown (new fields)
            $table->unsignedInteger('jumlah_seluruh_pekerja')->nullable()->after('tenaga_kerja_outsourcing');
            $table->unsignedInteger('pekerja_bukan_outsourcing_produksi')->nullable()->after('jumlah_seluruh_pekerja');
            $table->unsignedInteger('pekerja_bukan_outsourcing_lainnya')->nullable()->after('pekerja_bukan_outsourcing_produksi');
            $table->unsignedInteger('pekerja_outsourcing_produksi')->nullable()->after('pekerja_bukan_outsourcing_lainnya');
            $table->unsignedInteger('pekerja_outsourcing_lainnya')->nullable()->after('pekerja_outsourcing_produksi');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_seluruh_pekerja',
                'pekerja_bukan_outsourcing_produksi',
                'pekerja_bukan_outsourcing_lainnya',
                'pekerja_outsourcing_produksi',
                'pekerja_outsourcing_lainnya',
            ]);
        });
    }
};
