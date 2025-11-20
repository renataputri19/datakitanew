<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CORRECTED DESIGN: Organization-wide assessments (ONE per indicator)
     * - Removed kominfo_user_id field (was creating per-user assessments - WRONG!)
     * - Added UNIQUE constraint on indikator_id (one assessment per indicator)
     * - Added kominfo_created_by to track who first created the assessment
     * - Added kominfo_submitted_by to track who submitted it
     * - All Kominfo users collaborate on the SAME assessment data
     */
    public function up(): void
    {
        Schema::create('monalisa_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ONE assessment per indicator (organization-wide)
            $table->uuid('indikator_id')->unique(); // ← UNIQUE constraint for organization-wide assessments
            $table->foreign('indikator_id')->references('id')->on('monalisa_indikators')->onDelete('cascade');

            // Track Kominfo users who interacted (but don't create ownership)
            $table->uuid('kominfo_created_by')->nullable();
            $table->foreign('kominfo_created_by')->references('id')->on('users')->onDelete('set null');

            $table->uuid('kominfo_submitted_by')->nullable();
            $table->foreign('kominfo_submitted_by')->references('id')->on('users')->onDelete('set null');

            $table->uuid('kominfo_updated_by')->nullable();
            $table->foreign('kominfo_updated_by')->references('id')->on('users')->onDelete('set null');

            $table->uuid('kominfo_scored_by')->nullable();
            $table->foreign('kominfo_scored_by')->references('id')->on('users')->onDelete('set null');

            // BPS verification
            $table->uuid('bps_user_id')->nullable();
            $table->foreign('bps_user_id')->references('id')->on('users')->onDelete('set null');

            $table->uuid('bps_updated_by')->nullable();
            $table->foreign('bps_updated_by')->references('id')->on('users')->onDelete('set null');

            $table->uuid('bps_scored_by')->nullable();
            $table->foreign('bps_scored_by')->references('id')->on('users')->onDelete('set null');

            // Kominfo self-assessment (organization-wide)
            $table->integer('kominfo_maturity_level')->nullable(); // 1-5
            $table->text('kominfo_justification')->nullable();
            $table->timestamp('kominfo_submitted_at')->nullable();
            $table->timestamp('kominfo_updated_at')->nullable();

            // BPS verification/audit
            $table->integer('bps_maturity_level')->nullable(); // 1-5
            $table->text('bps_audit_comment')->nullable();
            $table->timestamp('bps_verified_at')->nullable();
            $table->timestamp('bps_updated_at')->nullable();

            // Status tracking
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected'])->default('draft');

            $table->timestamps();

            // Indexes
            $table->index('indikator_id');
            $table->index('kominfo_created_by');
            $table->index('kominfo_submitted_by');
            $table->index('kominfo_updated_by');
            $table->index('kominfo_scored_by');
            $table->index('bps_user_id');
            $table->index('bps_updated_by');
            $table->index('bps_scored_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_assessments');
    }
};
