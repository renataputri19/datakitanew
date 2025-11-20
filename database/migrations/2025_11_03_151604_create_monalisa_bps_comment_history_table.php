<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This table stores the complete history of all BPS comments and actions
     * on assessments, preserving the audit trail of verification, rejection,
     * and score updates.
     */
    public function up(): void
    {
        Schema::create('monalisa_bps_comment_history', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Assessment reference
            $table->uuid('assessment_id');
            $table->foreign('assessment_id')->references('id')->on('monalisa_assessments')->onDelete('cascade');

            // BPS user who made the comment
            $table->uuid('bps_user_id');
            $table->foreign('bps_user_id')->references('id')->on('users')->onDelete('cascade');

            // Comment and action details
            $table->text('comment');
            $table->enum('action_type', ['verified', 'rejected', 'score_updated'])->default('verified');
            $table->integer('bps_maturity_level')->nullable(); // Null for rejections

            $table->timestamps();

            // Indexes for efficient querying
            $table->index('assessment_id');
            $table->index('bps_user_id');
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_bps_comment_history');
    }
};

