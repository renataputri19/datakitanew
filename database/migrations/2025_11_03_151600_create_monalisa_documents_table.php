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
        Schema::create('monalisa_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assessment_id');
            $table->foreign('assessment_id')->references('id')->on('monalisa_assessments')->onDelete('cascade');
            $table->uuid('uploaded_by');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');

            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('file_type'); // pdf, xlsx, doc, etc.
            $table->integer('file_size'); // in bytes
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('assessment_id');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_documents');
    }
};
