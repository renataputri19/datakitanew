<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monalisa_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Who receives this notification
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('assessment_id');
            $table->foreign('assessment_id')->references('id')->on('monalisa_assessments')->onDelete('cascade');

            $table->uuid('triggered_by'); // Who triggered this notification
            $table->foreign('triggered_by')->references('id')->on('users')->onDelete('cascade');

            $table->enum('type', [
                'assessment_submitted',
                'assessment_updated',
                'assessment_verified',
                'assessment_rejected',
                'document_uploaded',
                'document_commented'
            ]);

            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('assessment_id');
            $table->index('triggered_by');
            $table->index('is_read');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_notifications');
    }
};
