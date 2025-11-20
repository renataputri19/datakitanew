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
        Schema::create('monalisa_document_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->foreign('document_id')->references('id')->on('monalisa_documents')->onDelete('cascade');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->text('comment');
            $table->enum('status', ['pass', 'fail', 'needs_revision', 'info'])->default('info');

            $table->timestamps();

            $table->index('document_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_document_comments');
    }
};
