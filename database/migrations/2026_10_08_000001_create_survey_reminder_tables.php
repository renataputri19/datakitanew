<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reminder emails for survey respondents, managed by BPS from /bps/pengingat.
 *
 * templates — BPS-editable subject/body with {placeholders}.
 * settings  — one row per survey: the automatic schedule and who it targets.
 * blasts    — one send run, automatic or manual. It keeps a copy of the
 *             template's subject/body, so editing or deleting a template
 *             never changes what history says was sent.
 * logs     — one email to one respondent. Rows are written `queued` and sent
 *            by the scheduler a batch per minute, so a web request never
 *            waits on SMTP. The unique key keeps a respondent to one email
 *            per survey period per day however many blasts target them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_reminder_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('survey_type', 20);
            $table->string('name', 100);
            $table->string('subject', 200);
            $table->text('body');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('survey_type');
        });

        Schema::create('survey_reminder_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('survey_type', 20)->unique();
            $table->foreignUuid('template_id')->nullable()->constrained('survey_reminder_templates')->nullOnDelete();
            $table->boolean('auto_enabled')->default(false);
            $table->json('days');
            $table->string('send_time', 5);
            $table->json('target_statuses');
            $table->json('excluded_kondisi');
            $table->string('from_name')->nullable();
            $table->string('reply_to')->nullable();
            $table->text('extra_message')->nullable();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('survey_reminder_blasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('survey_type', 20);
            $table->smallInteger('tahun')->unsigned();
            $table->tinyInteger('triwulan')->unsigned();
            $table->string('trigger', 10); // otomatis | manual
            $table->json('target_statuses');
            $table->foreignUuid('template_id')->nullable()->constrained('survey_reminder_templates')->nullOnDelete();
            $table->string('subject', 200);
            $table->text('body');
            $table->text('extra_message')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['survey_type', 'trigger', 'created_at']);
        });

        Schema::create('survey_reminder_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('blast_id')->constrained('survey_reminder_blasts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('survey_type', 20);
            $table->smallInteger('tahun')->unsigned();
            $table->tinyInteger('triwulan')->unsigned();
            $table->date('sent_on');
            $table->string('email');
            $table->string('nama_perusahaan')->nullable();
            $table->string('penghubung')->nullable();
            $table->boolean('in_progress')->default(false);
            $table->string('status', 10); // queued | sending | sent | failed
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'survey_type', 'tahun', 'triwulan', 'sent_on'], 'survey_reminder_logs_once_per_day');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_reminder_logs');
        Schema::dropIfExists('survey_reminder_blasts');
        Schema::dropIfExists('survey_reminder_settings');
        Schema::dropIfExists('survey_reminder_templates');
    }
};
