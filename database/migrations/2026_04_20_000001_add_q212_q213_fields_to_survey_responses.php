<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // Q202: Produk utama perusahaan (also used in Q210b)
            if (!Schema::hasColumn('survey_responses', 'produk_utama_perusahaan')) {
                $table->text('produk_utama_perusahaan')->nullable()->after('kbli_utama');
            }

            // Q210: Sertifikasi produk (free-text per category, matching paper form)
            if (!Schema::hasColumn('survey_responses', 'sertifikasi_keamanan_produk')) {
                $table->string('sertifikasi_keamanan_produk', 500)->nullable()->after('produk_utama_perusahaan');
            }
            if (!Schema::hasColumn('survey_responses', 'sertifikasi_kesehatan_keberlanjutan')) {
                $table->string('sertifikasi_kesehatan_keberlanjutan', 500)->nullable()->after('sertifikasi_keamanan_produk');
            }
            if (!Schema::hasColumn('survey_responses', 'sertifikasi_kualitas_manajemen')) {
                $table->string('sertifikasi_kualitas_manajemen', 500)->nullable()->after('sertifikasi_kesehatan_keberlanjutan');
            }
            if (!Schema::hasColumn('survey_responses', 'sertifikasi_tidak_ada')) {
                $table->string('sertifikasi_tidak_ada', 500)->nullable()->after('sertifikasi_kualitas_manajemen');
            }
            if (!Schema::hasColumn('survey_responses', 'sertifikasi_lainnya')) {
                $table->string('sertifikasi_lainnya', 500)->nullable()->after('sertifikasi_tidak_ada');
            }

            // Q213: Model industri manufaktur (multiple choice)
            if (!Schema::hasColumn('survey_responses', 'model_industri_oem')) {
                $table->tinyInteger('model_industri_oem')->nullable()->after('sertifikasi_lainnya');
            }
            if (!Schema::hasColumn('survey_responses', 'model_industri_odm')) {
                $table->tinyInteger('model_industri_odm')->nullable()->after('model_industri_oem');
            }
            if (!Schema::hasColumn('survey_responses', 'model_industri_obm')) {
                $table->tinyInteger('model_industri_obm')->nullable()->after('model_industri_odm');
            }
            if (!Schema::hasColumn('survey_responses', 'model_industri_tidak_ada')) {
                $table->tinyInteger('model_industri_tidak_ada')->nullable()->after('model_industri_obm');
            }
            if (!Schema::hasColumn('survey_responses', 'model_industri_lainnya')) {
                $table->string('model_industri_lainnya', 500)->nullable()->after('model_industri_tidak_ada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $columns = [
                'produk_utama_perusahaan',
                'sertifikasi_keamanan_produk',
                'sertifikasi_kesehatan_keberlanjutan',
                'sertifikasi_kualitas_manajemen',
                'sertifikasi_tidak_ada',
                'sertifikasi_lainnya',
                'model_industri_oem',
                'model_industri_odm',
                'model_industri_obm',
                'model_industri_tidak_ada',
                'model_industri_lainnya',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('survey_responses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
